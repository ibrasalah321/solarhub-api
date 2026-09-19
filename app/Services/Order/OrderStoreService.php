<?php

namespace App\Services\Order;

use App\Models\OrderStore;
use App\Models\StoreProduct;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class OrderStoreService
{
    /**
     * Status transitions a store owner is allowed to perform.
     * 'completed' is reached only through the customer's confirmDelivery() action.
     */
    private const ALLOWED_TRANSITIONS = [
        'pending' => ['accepted', 'rejected', 'cancelled'],
        'accepted' => ['shipped', 'cancelled'],
        'shipped' => ['delivered'],
    ];

    private const STOCK_RESTORING_STATUSES = ['rejected', 'cancelled'];

    /**
     * List the order-store branches belonging to the authenticated store owner.
     */
    public function getMyStoreOrders(User $storeOwner, ?string $status = null): Collection
    {
        $store = $storeOwner->store;

        abort_unless($store, 404, 'Store profile not found.');

        return $store->orderStores()
            ->with(['order.customer', 'items.storeProduct.masterProduct'])
            ->when($status, fn ($query) => $query->where('status', $status))
            ->latest()
            ->get();
    }

    /**
     * Update the status of an order-store branch, driven by the store owner.
     */
    public function updateStatus(User $storeOwner, OrderStore $orderStore, string $newStatus, ?string $notes = null): OrderStore
    {
        $this->ensureOwnership($storeOwner, $orderStore);

        $allowed = self::ALLOWED_TRANSITIONS[$orderStore->status] ?? [];

        abort_unless(
            in_array($newStatus, $allowed, true),
            422,
            "Cannot move an order from '{$orderStore->status}' to '{$newStatus}'."
        );

        return DB::transaction(function () use ($orderStore, $newStatus, $notes) {
            if (in_array($newStatus, self::STOCK_RESTORING_STATUSES, true)) {
                $orderStore->loadMissing('items');

                foreach ($orderStore->items as $item) {
                    $this->restoreStock($item->store_product_id, $item->quantity);
                }
            }

            $orderStore->update([
                'status' => $newStatus,
                'notes' => $notes ?? $orderStore->notes,
                'delivered_at' => $newStatus === 'delivered' ? now() : $orderStore->delivered_at,
            ]);

            return $orderStore->load(['order.customer', 'items.storeProduct.masterProduct']);
        });
    }

    /**
     * Customer confirms receipt of a delivered order-store branch.
     */
    public function confirmDelivery(User $customer, OrderStore $orderStore): OrderStore
    {
        abort_unless(
            $orderStore->order->customer_id === $customer->id,
            403,
            'You are not allowed to confirm this delivery.'
        );

        abort_unless(
            $orderStore->status === 'delivered',
            422,
            'Only a delivered order can be confirmed as completed.'
        );

        $orderStore->update([
            'status' => 'completed',
            'customer_confirmed_at' => now(),
        ]);

        return $orderStore->load(['order.customer', 'items.storeProduct.masterProduct']);
    }

    private function restoreStock(int $storeProductId, int $quantity): void
    {
        $storeProduct = StoreProduct::query()->lockForUpdate()->find($storeProductId);

        if (! $storeProduct) {
            return;
        }

        $storeProduct->increment('stock_quantity', $quantity);

        if ($storeProduct->status === 'out_of_stock' && $storeProduct->stock_quantity > 0) {
            $storeProduct->update(['status' => 'active']);
        }
    }

    private function ensureOwnership(User $storeOwner, OrderStore $orderStore): void
    {
        abort_unless(
            $orderStore->store->user_id === $storeOwner->id,
            403,
            'You are not allowed to manage this order.'
        );
    }
}

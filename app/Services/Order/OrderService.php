<?php

namespace App\Services\Order;

use App\Models\Order;
use App\Models\StoreProduct;
use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class OrderService
{
    /**
     * List orders belonging to the authenticated customer.
     */
    public function getMyOrders(User $customer): LengthAwarePaginator
    {
        return Order::query()
            ->where('customer_id', $customer->id)
            ->with(['governorate'])
            ->latest()
            ->paginate(15);
    }

    /**
     * Retrieve a single order with all of its details.
     */
    public function getOrderDetails(Order $order): Order
    {
        return $order->load([
            'customer',
            'governorate',
            'orderStores.store',
            'orderStores.items.storeProduct.masterProduct',
            'orderStores.payout',
        ]);
    }

    /**
     * Convert the authenticated customer's cart into a real order.
     *
     * The whole operation (order + order_stores + order_items + stock deduction)
     * runs inside a single DB transaction, with row-level locks on the
     * store_products being purchased to prevent overselling under concurrency.
     */
    public function createFromCart(User $customer, array $data): Order
    {
        $cart = $customer->cart()->with('items')->first();

        abort_if(
            ! $cart || $cart->items->isEmpty(),
            422,
            'Your cart is empty.'
        );

        return DB::transaction(function () use ($customer, $cart, $data) {
            // Lock every store_product involved to prevent concurrent overselling.
            $storeProductIds = $cart->items->pluck('store_product_id')->all();

            $storeProducts = StoreProduct::query()
                ->whereIn('id', $storeProductIds)
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            foreach ($cart->items as $cartItem) {
                $storeProduct = $storeProducts->get($cartItem->store_product_id);

                abort_unless(
                    $storeProduct && $storeProduct->status === 'active',
                    422,
                    'One of the products in your cart is no longer available.'
                );

                abort_if(
                    $cartItem->quantity > $storeProduct->stock_quantity,
                    422,
                    "Only {$storeProduct->stock_quantity} unit(s) of '{$storeProduct->masterProduct?->title}' are currently in stock."
                );
            }

            $order = Order::create([
                'order_number' => $this->generateOrderNumber(),
                'customer_id' => $customer->id,
                'delivery_governorate_id' => $data['delivery_governorate_id'] ?? null,
                'delivery_address' => $data['delivery_address'],
                'delivery_coordinates' => $data['delivery_coordinates'] ?? null,
                'total_amount' => 0,
                'status' => 'pending',
            ]);

            $orderTotal = 0;

            foreach ($cart->items->groupBy(fn ($item) => $storeProducts->get($item->store_product_id)->store_id) as $storeId => $items) {
                $storeSubtotal = $items->sum(
                    fn ($item) => $storeProducts->get($item->store_product_id)->price * $item->quantity
                );

                $orderStore = $order->orderStores()->create([
                    'store_id' => $storeId,
                    'subtotal' => $storeSubtotal,
                    'status' => 'pending',
                ]);

                foreach ($items as $cartItem) {
                    $storeProduct = $storeProducts->get($cartItem->store_product_id);

                    $orderStore->items()->create([
                        'store_product_id' => $storeProduct->id,
                        'unit_price' => $storeProduct->price,
                        'quantity' => $cartItem->quantity,
                        'total_price' => round($storeProduct->price * $cartItem->quantity, 2),
                    ]);

                    $this->deductStock($storeProduct, $cartItem->quantity);
                }

                $orderTotal += $storeSubtotal;
            }

            $order->update(['total_amount' => $orderTotal]);

            $cart->items()->delete();

            return $order->load([
                'customer',
                'governorate',
                'orderStores.store',
                'orderStores.items.storeProduct.masterProduct',
            ]);
        });
    }

    /**
     * Cancel a customer's order. Only allowed while every store branch of the
     * order is still 'pending' (i.e. no store has started processing it yet).
     * Deducted stock is restored for every item.
     */
    public function cancelOrder(User $customer, Order $order): Order
    {
        abort_unless(
            $order->customer_id === $customer->id,
            403,
            'You are not allowed to cancel this order.'
        );

        $order->loadMissing('orderStores.items');

        abort_if(
            $order->orderStores->contains(fn ($orderStore) => $orderStore->status !== 'pending'),
            422,
            'This order can no longer be cancelled because at least one store has already started processing it.'
        );

        return DB::transaction(function () use ($order) {
            foreach ($order->orderStores as $orderStore) {
                foreach ($orderStore->items as $item) {
                    $this->restoreStock($item->store_product_id, $item->quantity);
                }

                $orderStore->update(['status' => 'cancelled']);
            }

            $order->update(['status' => 'cancelled']);

            return $order->load(['orderStores.items']);
        });
    }

    private function deductStock(StoreProduct $storeProduct, int $quantity): void
    {
        $storeProduct->decrement('stock_quantity', $quantity);

        if ($storeProduct->stock_quantity <= 0) {
            $storeProduct->update(['status' => 'out_of_stock']);
        }
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

    private function generateOrderNumber(): string
    {
        do {
            $orderNumber = 'ORD-' . now()->format('Ymd') . '-' . strtoupper(Str::random(6));
        } while (Order::query()->where('order_number', $orderNumber)->exists());

        return $orderNumber;
    }
}

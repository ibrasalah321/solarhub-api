<?php

namespace App\Services\Payment;

use App\Models\OrderStore;
use App\Models\StorePayout;
use App\Models\User;
use App\Models\UserWallet;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class StorePayoutService
{
    private const ELIGIBLE_ORDER_STORE_STATUSES = ['delivered', 'completed'];

    /**
     * List payouts, optionally filtered by status.
     */
    public function list(?string $status = null): LengthAwarePaginator
    {
        return StorePayout::query()
            ->with(['orderStore.order', 'orderStore.store', 'userWallet.walletProvider'])
            ->when($status, fn (Builder $query) => $query->where('status', $status))
            ->latest()
            ->paginate((int) request()->input('per_page', 100));
    }

    /**
     * List payouts for a specific store owner.
     */
    public function getMyStorePayouts(User $storeOwner, ?string $status = null): \Illuminate\Database\Eloquent\Collection
    {
        $store = $storeOwner->store;
        if (!$store) {
            return new \Illuminate\Database\Eloquent\Collection();
        }

        return StorePayout::query()
            ->whereHas('orderStore', fn (Builder $q) => $q->where('store_id', $store->id))
            ->when($status, fn (Builder $q) => $q->where('status', $status))
            ->with(['orderStore.order', 'orderStore.store', 'userWallet.walletProvider'])
            ->latest()
            ->get();
    }

    /**
     * Calculate financial summary for a store owner.
     */
    public function getFinancialSummary(User $storeOwner): array
    {
        $store = $storeOwner->store;
        if (!$store) {
            return [
                'total_settled' => 0.0,
                'total_pending' => 0.0,
                'total_commissions' => 0.0,
                'net_available' => 0.0,
                'settlements_count' => 0,
            ];
        }

        $payouts = StorePayout::query()
            ->whereHas('orderStore', fn (Builder $q) => $q->where('store_id', $store->id))
            ->get();

        $totalSettled = (float) $payouts->where('status', 'completed')->sum('net_amount');
        $totalPending = (float) $payouts->where('status', 'pending')->sum('net_amount');
        $totalCommissions = (float) $payouts->sum('commission_amount');

        return [
            'total_settled' => round($totalSettled, 2),
            'total_pending' => round($totalPending, 2),
            'total_commissions' => round($totalCommissions, 2),
            'net_available' => round($totalSettled, 2),
            'settlements_count' => $payouts->count(),
        ];
    }

    /**
     * Create a payout for a completed order-store, computing the commission
     * and net amount from the order-store subtotal. Financial figures are
     * never accepted from the client.
     */
    public function createPayout(User $admin, array $data): StorePayout
    {
        $orderStore = OrderStore::query()->findOrFail($data['order_store_id']);

        abort_if(
            $orderStore->payout()->exists(),
            409,
            'A payout has already been created for this order-store.'
        );

        abort_unless(
            in_array($orderStore->status, self::ELIGIBLE_ORDER_STORE_STATUSES, true),
            422,
            'A payout can only be created once the order has been delivered or completed.'
        );

        return DB::transaction(function () use ($orderStore, $data) {
            $totalAmount = (float) $orderStore->subtotal;
            $commissionRate = (float) $data['commission_rate'];
            $commissionAmount = round($totalAmount * $commissionRate / 100, 2);
            $netAmount = round($totalAmount - $commissionAmount, 2);

            $userWallet = $this->resolveWallet($orderStore, $data['user_wallet_id'] ?? null);

            $payout = StorePayout::create([
                'order_store_id' => $orderStore->id,
                'user_wallet_id' => $userWallet?->id,
                'total_amount' => $totalAmount,
                'commission_rate' => $commissionRate,
                'commission_amount' => $commissionAmount,
                'net_amount' => $netAmount,
                'status' => 'pending',
                'transfer_reference' => $data['transfer_reference'] ?? null,
            ]);

            return $payout->load(['orderStore.store', 'userWallet.walletProvider']);
        });
    }

    /**
     * Mark a pending payout as completed.
     */
    public function markCompleted(StorePayout $payout, ?string $transferReference = null): StorePayout
    {
        $this->ensurePending($payout);

        $payout->update([
            'status' => 'completed',
            'transfer_reference' => $transferReference ?? $payout->transfer_reference,
            'paid_at' => now(),
        ]);

        return $payout->load(['orderStore.store', 'userWallet.walletProvider']);
    }

    /**
     * Mark a pending payout as failed.
     */
    public function markFailed(StorePayout $payout, ?string $transferReference = null): StorePayout
    {
        $this->ensurePending($payout);

        $payout->update([
            'status' => 'failed',
            'transfer_reference' => $transferReference ?? $payout->transfer_reference,
        ]);

        return $payout->load(['orderStore.store', 'userWallet.walletProvider']);
    }

    private function resolveWallet(OrderStore $orderStore, ?int $userWalletId): ?UserWallet
    {
        $storeOwnerId = $orderStore->store->user_id;

        if ($userWalletId) {
            $wallet = UserWallet::query()->findOrFail($userWalletId);

            abort_unless(
                $wallet->user_id === $storeOwnerId,
                422,
                'The selected wallet does not belong to this store owner.'
            );

            return $wallet;
        }

        return UserWallet::query()
            ->where('user_id', $storeOwnerId)
            ->where('is_default', true)
            ->where('is_active', true)
            ->first();
    }

    private function ensurePending(StorePayout $payout): void
    {
        abort_unless(
            $payout->status === 'pending',
            422,
            'Only pending payouts can be updated.'
        );
    }
}

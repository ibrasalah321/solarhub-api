<?php

namespace App\Services\Payment;

use App\Models\Order;
use App\Models\OrderPayment;
use App\Models\User;
use App\Services\SupabaseStorageService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;

class OrderPaymentService
{
    public function __construct(
        private readonly SupabaseStorageService $storageService
    ) {
    }

    /**
     * List payments for a specific order.
     */
    public function getForOrder(Order $order): Collection
    {
        return $order->payments()
            ->with(['walletProvider', 'verifiedBy'])
            ->latest()
            ->get();
    }

    /**
     * Submit a new payment proof for an order.
     */
    public function submitPayment(User $customer, array $data, UploadedFile $receipt): OrderPayment
    {
        $order = Order::query()->findOrFail($data['order_id']);

        abort_unless(
            $order->customer_id === $customer->id,
            403,
            'You are not allowed to submit a payment for this order.'
        );

        abort_if(
            $order->status === 'cancelled',
            422,
            'Cannot submit a payment for a cancelled order.'
        );

        return DB::transaction(function () use ($order, $data, $receipt) {
            $receiptPath = $this->storageService->uploadPrivate(
                $receipt,
                "orders/{$order->id}/payment-receipts"
            );

            return $order->payments()->create([
                'wallet_provider_id' => $data['wallet_provider_id'] ?? null,
                'amount' => $data['amount'],
                'transaction_reference' => $data['transaction_reference'] ?? null,
                'receipt_image' => $receiptPath,
                'status' => 'pending',
                'paid_at' => $data['paid_at'] ?? now(),
            ])->load(['walletProvider', 'verifiedBy']);
        });
    }

    /**
     * Verify a pending payment.
     */
    public function verify(User $admin, OrderPayment $payment): OrderPayment
    {
        $this->ensurePending($payment);

        $payment->update([
            'status' => 'verified',
            'verified_by' => $admin->id,
            'verified_at' => now(),
        ]);

        return $payment->load(['walletProvider', 'verifiedBy']);
    }

    /**
     * Reject a pending payment.
     */
    public function reject(User $admin, OrderPayment $payment): OrderPayment
    {
        $this->ensurePending($payment);

        $payment->update([
            'status' => 'rejected',
            'verified_by' => $admin->id,
            'verified_at' => now(),
        ]);

        return $payment->load(['walletProvider', 'verifiedBy']);
    }

    private function ensurePending(OrderPayment $payment): void
    {
        abort_unless(
            $payment->status === 'pending',
            422,
            'Only pending payments can be verified or rejected.'
        );
    }
}

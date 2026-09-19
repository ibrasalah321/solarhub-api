<?php

namespace App\Services\Payment;

use App\Models\Order;
use App\Models\OrderPayment;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class OrderPaymentService
{
    public function submitPayment(array $data, UploadedFile $receiptFile): OrderPayment
    {
        return DB::transaction(function () use ($data, $receiptFile) {
            $order = Order::findOrFail($data['order_id']);

            if (in_array($order->status, ['cancelled', 'completed'])) {
                throw ValidationException::withMessages([
                    'order_id' => 'لا يمكن رفع دفعة لطلب ملغي أو مكتمل مسبقاً.',
                ]);
            }

            // رفع الإيصال في الحاوية الخاصة المحمية
            $path = $receiptFile->store('payment_receipts', 'supabase_private');

            return OrderPayment::create([
                'order_id' => $order->id,
                'wallet_provider_id' => $data['wallet_provider_id'],
                'amount' => $data['amount'],
                'transaction_reference' => $data['transaction_reference'],
                'receipt_image' => $path,
                'status' => 'pending',
                'paid_at' => now(),
            ]);
        });
    }

    public function verifyPayment(OrderPayment $payment, int $adminId, string $status, ?string $notes = null): OrderPayment
    {
        return DB::transaction(function () use ($payment, $adminId, $status, $notes) {
            $payment->update([
                'status' => $status,
                'verified_by' => $adminId,
                'verified_at' => now(),
                'notes' => $notes,
            ]);

            if ($status === 'approved') {
                $payment->order->update(['status' => 'processing']);
            }

            return $payment;
        });
    }
}
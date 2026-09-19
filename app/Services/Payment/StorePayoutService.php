<?php

namespace App\Services\Payment;

use App\Models\OrderStore;
use App\Models\Store;
use App\Models\StorePayout;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class StorePayoutService
{
    public function generatePayout(Store $store, float $commissionPercentage = 5.0): StorePayout
    {
        return DB::transaction(function () use ($store, $commissionPercentage) {
            // جلب طلبات المتجر المكتملة التي لم تتم تصفيتها
            $unpaidOrders = OrderStore::where('store_id', $store->id)
                ->where('status', 'completed')
                ->whereNull('payout_id')
                ->lockForUpdate()
                ->get();

            if ($unpaidOrders->isEmpty()) {
                throw ValidationException::withMessages([
                    'store_id' => 'لا توجد طلبات مكتملة جاهزة للتصفية لهذا المتجر.',
                ]);
            }

            $totalSales = $unpaidOrders->sum('subtotal');
            $commissionAmount = round(($totalSales * ($commissionPercentage / 100)), 2);
            $netAmount = $totalSales - $commissionAmount;

            $payout = StorePayout::create([
                'store_id' => $store->id,
                'total_amount' => $totalSales,
                'commission_amount' => $commissionAmount,
                'net_amount' => $netAmount,
                'status' => 'pending',
            ]);

            OrderStore::whereIn('id', $unpaidOrders->pluck('id'))
                ->update(['payout_id' => $payout->id]);

            return $payout;
        });
    }

    public function markAsTransferred(StorePayout $payout, string $transferReference): StorePayout
    {
        $payout->update([
            'status' => 'transferred',
            'transfer_reference' => $transferReference,
            'transferred_at' => now(),
        ]);

        return $payout;
    }
}
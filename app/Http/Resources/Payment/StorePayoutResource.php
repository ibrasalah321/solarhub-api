<?php

namespace App\Http\Resources\Payment;

use App\Http\Resources\Wallet\UserWalletResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StorePayoutResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $walletProvider = $this->userWallet?->walletProvider;
        $order = $this->orderStore?->order;

        return [
            'id' => $this->id,
            'order_store_id' => $this->order_store_id,
            'order_number' => $order?->order_number ?? ('#ORD-' . ($this->order_store_id ?? $this->id)),
            'order_date' => $order?->created_at ?? $this->created_at,

            'store' => $this->whenLoaded('orderStore', fn () => [
                'id' => $this->orderStore?->store?->id,
                'company_name' => $this->orderStore?->store?->company_name,
            ]),

            'user_wallet' => new UserWalletResource(
                $this->whenLoaded('userWallet')
            ),

            'bank_or_wallet' => $walletProvider?->name_ar ?? $walletProvider?->name ?? 'محفظة إلكترونية معتمدة',
            'account_number' => $this->userWallet?->account_number,
            'account_holder_name' => $this->userWallet?->account_holder_name,

            'total_amount' => (float) $this->total_amount,
            'commission_rate' => (float) $this->commission_rate,
            'commission_amount' => (float) $this->commission_amount,
            'net_amount' => (float) $this->net_amount,
            'status' => $this->status,
            'transfer_reference' => $this->transfer_reference,
            'paid_at' => $this->paid_at,
            'created_at' => $this->created_at,
        ];
    }
}

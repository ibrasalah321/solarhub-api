<?php

namespace App\Http\Resources\Payment;

use App\Http\Resources\Wallet\UserWalletResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StorePayoutResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'order_store_id' => $this->order_store_id,

            'store' => $this->whenLoaded('orderStore', fn () => [
                'id' => $this->orderStore?->store?->id,
                'company_name' => $this->orderStore?->store?->company_name,
            ]),

            'user_wallet' => new UserWalletResource(
                $this->whenLoaded('userWallet')
            ),

            'total_amount' => $this->total_amount,
            'commission_rate' => $this->commission_rate,
            'commission_amount' => $this->commission_amount,
            'net_amount' => $this->net_amount,
            'status' => $this->status,
            'transfer_reference' => $this->transfer_reference,
            'paid_at' => $this->paid_at,
            'created_at' => $this->created_at,
        ];
    }
}

<?php

namespace App\Http\Resources\Payment;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StorePayoutResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'store_id' => $this->store_id,
            'store_name' => $this->store?->company_name,
            'total_sales' => (float) $this->total_amount,
            'platform_commission' => (float) $this->commission_amount,
            'net_payout' => (float) $this->net_amount,
            'status' => $this->status,
            'transfer_reference' => $this->transfer_reference,
            'transferred_at' => $this->transferred_at?->toIso8601String(),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
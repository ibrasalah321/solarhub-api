<?php

namespace App\Http\Resources\Order;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class QuoteRequestResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $storeProduct = $this->whenLoaded('storeProduct');

        return [
            'id' => $this->id,

            'customer' => $this->whenLoaded('customer', fn () => [
                'id' => $this->customer->id,
                'name' => $this->customer->name,
            ]),

            'store_product' => [
                'id' => $storeProduct?->id,
                'title' => $storeProduct?->masterProduct?->title,
                'listed_price' => $storeProduct?->price,
                'store' => [
                    'id' => $storeProduct?->store?->id,
                    'company_name' => $storeProduct?->store?->company_name,
                ],
            ],

            'quantity' => $this->quantity,
            'customer_target_price' => $this->customer_target_price,
            'offered_unit_price' => $this->offered_unit_price,
            'total_price' => $this->total_price,
            'status' => $this->status,
            'customer_notes' => $this->customer_notes,
            'store_notes' => $this->store_notes,
            'responded_at' => $this->responded_at,
            'accepted_at' => $this->accepted_at,
            'created_at' => $this->created_at,
        ];
    }
}

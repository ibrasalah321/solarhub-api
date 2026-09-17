<?php

namespace App\Http\Resources\Order;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'order_number' => $this->order_number,

            'customer' => $this->whenLoaded('customer', fn () => [
                'id' => $this->customer->id,
                'name' => $this->customer->name,
                'phone' => $this->customer->phone,
            ]),

            'governorate' => $this->whenLoaded('governorate', fn () => [
                'id' => $this->governorate?->id,
                'name' => $this->governorate?->name_ar,
            ]),

            'order_stores' => OrderStoreResource::collection(
                $this->whenLoaded('orderStores')
            ),

            'total_amount' => $this->total_amount,
            'delivery_address' => $this->delivery_address,
            'delivery_coordinates' => $this->delivery_coordinates,
            'status' => $this->status,
            'created_at' => $this->created_at,
        ];
    }
}

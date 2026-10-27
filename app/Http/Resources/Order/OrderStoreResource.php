<?php

namespace App\Http\Resources\Order;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderStoreResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'order_id' => $this->order_id,

            'order' => $this->whenLoaded('order', fn () => [
                'id' => $this->order->id,
                'order_number' => $this->order->order_number,
                'total_amount' => $this->order->total_amount,
                'delivery_address' => $this->order->delivery_address,
                'status' => $this->order->status,
                'governorate' => $this->order->governorate?->name_ar,
                'customer' => [
                    'id' => $this->order->customer?->id,
                    'name' => $this->order->customer?->name,
                    'phone' => $this->order->customer?->phone,
                    'email' => $this->order->customer?->email,
                ],
                'created_at' => $this->order->created_at,
            ]),

            'store' => $this->whenLoaded('store', fn () => [
                'id' => $this->store->id,
                'company_name' => $this->store->company_name,
                'whatsapp_number' => $this->store->whatsapp_number,
            ]),

            'items' => OrderItemResource::collection(
                $this->whenLoaded('items')
            ),

            'subtotal' => $this->subtotal,
            'status' => $this->status,
            'notes' => $this->notes,
            'delivered_at' => $this->delivered_at,
            'customer_confirmed_at' => $this->customer_confirmed_at,

            'has_payout' => $this->whenLoaded('payout', fn () => (bool) $this->payout),

            'created_at' => $this->created_at,
        ];
    }
}

<?php

namespace App\Http\Resources\Order;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderItemResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $storeProduct = $this->whenLoaded('storeProduct');

        return [
            'id' => $this->id,
            'product_title' => $storeProduct?->masterProduct?->title,
            'unit_price' => $this->unit_price,
            'quantity' => $this->quantity,
            'total_price' => $this->total_price,
        ];
    }
}

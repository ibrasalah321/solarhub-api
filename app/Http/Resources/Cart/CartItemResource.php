<?php

namespace App\Http\Resources\Cart;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CartItemResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $storeProduct = $this->whenLoaded('storeProduct');

        return [
            'id' => $this->id,
            'quantity' => $this->quantity,

            'store_product' => [
                'id' => $storeProduct?->id,
                'title' => $storeProduct?->masterProduct?->title,
                'price' => $storeProduct?->price,
                'stock_quantity' => $storeProduct?->stock_quantity,
                'min_order_qty' => $storeProduct?->min_order_qty,
                'store' => [
                    'id' => $storeProduct?->store?->id,
                    'company_name' => $storeProduct?->store?->company_name,
                ],
            ],

            'line_total' => $storeProduct
                ? round($storeProduct->price * $this->quantity, 2)
                : null,
        ];
    }
}

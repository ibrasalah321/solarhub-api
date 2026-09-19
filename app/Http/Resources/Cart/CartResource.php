<?php

namespace App\Http\Resources\Cart;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CartResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $items = $this->relationLoaded('items') ? $this->items : collect();

        return [
            'id' => $this->id,
            'items' => CartItemResource::collection($items),
            'items_count' => $items->sum('quantity'),
            'total_amount' => $items->sum(
                fn ($item) => $item->storeProduct ? $item->storeProduct->price * $item->quantity : 0
            ),
        ];
    }
}

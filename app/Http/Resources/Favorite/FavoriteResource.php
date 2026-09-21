<?php

namespace App\Http\Resources\Favorite;

use App\Http\Resources\Store\StoreProductResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FavoriteResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'store_product_id' => $this->store_product_id,
            'created_at' => $this->created_at?->toIso8601String(),
            'product' => $this->whenLoaded('storeProduct', function () {
                return new StoreProductResource($this->storeProduct);
            }),
        ];
    }
}
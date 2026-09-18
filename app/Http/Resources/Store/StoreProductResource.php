<?php

namespace App\Http\Resources\Store;

use App\Http\Resources\Catalog\MasterProductResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StoreProductResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,

            'master_product' => new MasterProductResource(
                $this->whenLoaded('masterProduct')
            ),

            'store' => $this->whenLoaded('store', fn () => [
                'id' => $this->store->id,
                'company_name' => $this->store->company_name,
            ]),

            'governorate' => $this->whenLoaded('governorate', fn () => [
                'id' => $this->governorate?->id,
                'name_ar' => $this->governorate?->name_ar,
            ]),

            'price' => $this->price,
            'stock_quantity' => $this->stock_quantity,
            'min_order_qty' => $this->min_order_qty,
            'warranty_period' => $this->warranty_period,
            'status' => $this->status,
            'created_at' => $this->created_at,
        ];
    }
}

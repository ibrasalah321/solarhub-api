<?php

namespace App\Http\Resources\Order;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderItemResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $storeProduct = $this->whenLoaded('storeProduct');
        $master = $storeProduct?->masterProduct;
        $imagePath = $master?->images?->first()?->image_path;
        $imageUrl = $imagePath ? app(\App\Services\SupabaseStorageService::class)->publicUrl($imagePath) : null;

        return [
            'id' => $this->id,
            'store_product_id' => $this->store_product_id,
            'product_title' => $master?->title ?? 'منتج طاقة شمسية',
            'product_model' => $master?->model_number ?? '',
            'brand' => $master?->brand?->name ?? '',
            'category' => $master?->category?->name_ar ?? '',
            'warranty_period' => $storeProduct?->warranty_period ?? '–',
            'image' => $imageUrl,
            'unit_price' => $this->unit_price,
            'quantity' => $this->quantity,
            'total_price' => $this->total_price,
        ];
    }
}

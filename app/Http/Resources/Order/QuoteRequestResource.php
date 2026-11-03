<?php

namespace App\Http\Resources\Order;

use App\Services\SupabaseStorageService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class QuoteRequestResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $storeProduct = $this->whenLoaded('storeProduct');
        $master = $storeProduct?->masterProduct;
        $imagePath = $master?->images?->first()?->image_path;
        $imageUrl = $imagePath ? app(SupabaseStorageService::class)->publicUrl($imagePath) : null;

        return [
            'id' => $this->id,
            'quote_number' => '#RFQ-' . str_pad((string) $this->id, 4, '0', STR_PAD_LEFT),

            'customer' => $this->whenLoaded('customer', fn () => [
                'id' => $this->customer?->id,
                'name' => $this->customer?->name,
                'phone' => $this->customer?->phone,
                'email' => $this->customer?->email,
            ]),

            'store_product' => [
                'id' => $storeProduct?->id,
                'title' => $master?->title ?? 'منتج طاقة شمسية',
                'model_number' => $master?->model_number ?? '',
                'sku' => $storeProduct?->sku ?? $master?->sku ?? '',
                'brand' => $master?->brand?->name ?? '',
                'category' => $master?->category?->name_ar ?? $master?->category?->name ?? '',
                'image' => $imageUrl,
                'warranty_period' => $storeProduct?->warranty_period ?? '–',
                'governorate' => $storeProduct?->governorate?->name ?? '–',
                'listed_price' => $storeProduct?->price ? (float) $storeProduct->price : null,
                'store' => [
                    'id' => $storeProduct?->store?->id,
                    'company_name' => $storeProduct?->store?->company_name,
                ],
            ],

            'quantity' => (int) $this->quantity,
            'customer_target_price' => $this->customer_target_price !== null ? (float) $this->customer_target_price : null,
            'offered_unit_price' => $this->offered_unit_price !== null ? (float) $this->offered_unit_price : null,
            'total_price' => $this->total_price !== null ? (float) $this->total_price : null,
            'status' => $this->status,
            'customer_notes' => $this->customer_notes,
            'store_notes' => $this->store_notes,
            'responded_at' => $this->responded_at,
            'accepted_at' => $this->accepted_at,
            'created_at' => $this->created_at,
        ];
    }
}

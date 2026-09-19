<?php

namespace App\Http\Resources\Catalog;

use App\Services\SupabaseStorageService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MasterProductResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'model_number' => $this->model_number,
            'description' => $this->description,

            'datasheet_url' => app(SupabaseStorageService::class)->publicUrl($this->datasheet_file),

            'category' => $this->whenLoaded('category', fn () => [
                'id' => $this->category->id,
                'name_ar' => $this->category->name_ar,
                'name_en' => $this->category->name_en,
            ]),

            'brand' => $this->whenLoaded('brand', fn () => [
                'id' => $this->brand->id,
                'name' => $this->brand->name,
            ]),

            'specifications' => ProductSpecificationResource::collection(
                $this->whenLoaded('specifications')
            ),

            'images' => ProductImageResource::collection(
                $this->whenLoaded('images')
            ),

            'stores_count' => $this->whenCounted('storeProducts'),

            'is_active' => $this->is_active,
            'created_at' => $this->created_at,
        ];
    }
}

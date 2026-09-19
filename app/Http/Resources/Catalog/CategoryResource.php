<?php

namespace App\Http\Resources\Catalog;

use App\Services\SupabaseStorageService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CategoryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'parent_id' => $this->parent_id,
            'name_ar' => $this->name_ar,
            'name_en' => $this->name_en,
            'slug' => $this->slug,
            'icon_url' => app(SupabaseStorageService::class)->publicUrl($this->icon),

            'children' => CategoryResource::collection(
                $this->whenLoaded('children')
            ),

            'products_count' => $this->whenCounted('masterProducts'),
        ];
    }
}

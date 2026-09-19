<?php

namespace App\Http\Resources\Catalog;

use App\Services\SupabaseStorageService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductImageResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'url' => app(SupabaseStorageService::class)->publicUrl($this->image_path),
            'is_featured' => $this->is_featured,
        ];
    }
}

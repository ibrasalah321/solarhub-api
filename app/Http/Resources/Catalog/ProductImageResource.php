<?php

namespace App\Http\Resources\Catalog;

use App\Services\SupabaseStorageService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductImageResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $url = app(SupabaseStorageService::class)->publicUrl($this->image_path);

        return [
            'id' => $this->id,
            'url' => $url,
            'image_url' => $url,
            'image_path' => $this->image_path,
            'is_featured' => (bool) $this->is_featured,
        ];
    }
}

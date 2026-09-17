<?php

namespace App\Http\Resources\Wallet;

use App\Services\SupabaseStorageService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class WalletProviderResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'code' => $this->code,
            'name_ar' => $this->name_ar,
            'name_en' => $this->name_en,
            'logo_url' => app(SupabaseStorageService::class)
                ->publicUrl($this->logo_path),
            'is_active' => $this->is_active,
            'created_at' => $this->created_at,
        ];
    }
}

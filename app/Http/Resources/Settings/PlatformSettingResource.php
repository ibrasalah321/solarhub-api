<?php

namespace App\Http\Resources\Settings;

use App\Services\SupabaseStorageService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PlatformSettingResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $storage = app(SupabaseStorageService::class);

        return [
            'id' => $this->id,
            'platform_name' => $this->platform_name,
            'support_phone' => $this->support_phone,
            'support_email' => $this->support_email,
            'logo_url' => $storage->publicUrl($this->logo_path),
            'favicon_url' => $storage->publicUrl($this->favicon_path),
            'currency' => $this->currency,
            'is_maintenance_mode' => $this->is_maintenance_mode,
            'updated_at' => $this->updated_at,
        ];
    }
}

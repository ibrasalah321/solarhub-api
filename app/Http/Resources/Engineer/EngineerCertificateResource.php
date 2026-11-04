<?php

namespace App\Http\Resources\Engineer;

use App\Services\SupabaseStorageService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EngineerCertificateResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $fileUrl = $this->file_path
            ? app(SupabaseStorageService::class)->temporaryPrivateUrl($this->file_path, 60)
            : null;

        return [
            'id' => $this->id,
            'file_path' => $this->file_path,
            'file_url' => $fileUrl,
            'created_at' => $this->created_at,
        ];
    }
}

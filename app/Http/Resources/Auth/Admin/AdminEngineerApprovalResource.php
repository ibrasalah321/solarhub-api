<?php

namespace App\Http\Resources\Auth\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Services\SupabaseStorageService;

class AdminEngineerApprovalResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $storage = app(SupabaseStorageService::class);
        return [
            'id' => $this->id,
            'name' => $this->user->name,
            'email' => $this->user->email,
            'phone' => $this->user->phone,
            'cv_url' => $storage->temporaryPrivateUrl($this->cv_path,15),
            'license_number' => $this->license_number,
            'approval_status' => $this->approval_status
        ];
    }
}
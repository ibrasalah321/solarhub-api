<?php

namespace App\Http\Resources\Auth\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Services\SupabaseStorageService;

class AdminStoreApprovalResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $storage = app(SupabaseStorageService::class);
        return [
            'id' => $this->id,
            'name' => $this->user->name,
            'email' => $this->user->email,
            'phone' => $this->user->phone,
            'company_name' =>$this->company_name,
            'store_type' =>$this->store_type,
            'commercial_registry' =>$this->commercial_registry,
            'commercial_file_url' => $storage->temporaryPrivateUrl($this->commercial_file_path,15),
            'approval_status' => $this->approval_status
        ];
    }
}
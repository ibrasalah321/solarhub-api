<?php

namespace App\Http\Resources\Auth\Admin;

use App\Services\SupabaseStorageService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AdminStoreApprovalResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $storage = app(SupabaseStorageService::class);

        return [
            'id' => $this->id,
            'name' => $this->user?->name,
            'owner' => $this->user?->name,
            'email' => $this->user?->email,
            'phone' => $this->user?->phone,
            'company_name' => $this->company_name,
            'store_type' => $this->store_type,
            'commercial_registry' => $this->commercial_registry,
            'commercial_file_url' => $this->commercial_file_path ? $storage->temporaryPrivateUrl($this->commercial_file_path, 15) : null,
            'tax_number' => $this->tax_number,
            'bio' => $this->bio,
            'whatsapp_number' => $this->whatsapp_number,
            'address_details' => $this->address_details,
            'location_coordinates' => $this->location_coordinates,
            'governorate_id' => $this->user?->governorate_id,
            'governorate' => $this->user?->governorate?->name_ar ?? $this->user?->governorate?->name_en ?? $this->user?->governorate?->name,
            'governorate_name' => $this->user?->governorate?->name_ar ?? $this->user?->governorate?->name_en ?? $this->user?->governorate?->name,
            'approval_status' => $this->approval_status,
            'rejection_reason' => $this->rejection_reason,
            'created_at' => $this->created_at,
            'approved_at' => $this->approved_at,
        ];
    }
}

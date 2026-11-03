<?php

namespace App\Http\Resources\Auth\Admin;

use App\Services\SupabaseStorageService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AdminEngineerApprovalResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $storage = app(SupabaseStorageService::class);

        return [
            'id' => $this->id,
            'name' => $this->user?->name,
            'email' => $this->user?->email,
            'phone' => $this->user?->phone,
            'governorate_id' => $this->user?->governorate_id,
            'governorate' => $this->user?->governorate?->name_ar ?? $this->user?->governorate?->name_en ?? $this->user?->governorate?->name,
            'governorate_name' => $this->user?->governorate?->name_ar ?? $this->user?->governorate?->name_en ?? $this->user?->governorate?->name,
            'location_coordinates' => $this->user?->default_coordinates,
            'cv_url' => $this->cv_path ? $storage->temporaryPrivateUrl($this->cv_path, 15) : null,
            'license_number' => $this->license_number,
            'years_of_experience' => $this->years_of_experience,
            'bio' => $this->bio,
            'specializations' => $this->specializations?->map(fn ($s) => [
                'id' => $s->id,
                'name' => $s->name,
            ]) ?? [],
            'approval_status' => $this->approval_status,
            'approved_at' => $this->approved_at,
            'created_at' => $this->created_at,
        ];
    }
}

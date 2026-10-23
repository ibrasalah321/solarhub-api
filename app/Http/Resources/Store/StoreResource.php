<?php

namespace App\Http\Resources\Store;

use App\Services\SupabaseStorageService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StoreResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $storage = app(SupabaseStorageService::class);
        $user = $request->user();
        $isOwnerOrAdmin = $user && ($user->id === $this->user_id || $user->hasRole('admin'));

        return [
            'id' => $this->id,
            'company_name' => $this->company_name,
            'owner' => $this->user?->name,
            'email' => $this->user?->email,
            'phone' => $this->user?->phone,
            'governorate_id' => $this->user?->governorate_id,
            'governorate' => $this->user?->governorate?->name_ar ?? $this->user?->governorate?->name_en ?? $this->user?->governorate?->name,
            'governorate_name' => $this->user?->governorate?->name_ar ?? $this->user?->governorate?->name_en ?? $this->user?->governorate?->name,
            'bio' => $this->bio,
            'company_logo_url' => $storage->publicUrl($this->company_logo_path),
            'whatsapp_number' => $this->whatsapp_number,
            'store_type' => $this->store_type,
            'approval_status' => $this->approval_status,
            'address_details' => $this->address_details,
            'location_coordinates' => $this->location_coordinates,

            'average_rating' => round((float) ($this->ratings_avg_rating ?? 0), 1),
            'ratings_count' => (int) ($this->approved_ratings_count ?? 0),

            'commercial_registry' => $this->commercial_registry,
            'tax_number' => $this->tax_number,
            'commercial_file_url' => $this->commercial_file_path ? $storage->temporaryPrivateUrl($this->commercial_file_path, 15) : null,
            'rejection_reason' => $this->rejection_reason,

            'created_at' => $this->created_at,
        ];
    }
}

// GET /api/admin/engineers
// Post /api/admin/engineer/approve
// Post /api/admin/engineer/reject
// GET /api/admin/stores
// Post /api/admin/store/approve
// Post /api/admin/store/reject

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
        $isOwnerOrAdmin = $request->user()?->id === $this->user_id;

        return [
            'id' => $this->id,
            'company_name' => $this->company_name,
            'bio' => $this->bio,
            'company_logo_url' => $storage->publicUrl($this->company_logo_path),
            'whatsapp_number' => $this->whatsapp_number,
            'store_type' => $this->store_type,
            'approval_status' => $this->approval_status,
            'address_details' => $this->address_details,
            'location_coordinates' => $this->location_coordinates,

            'average_rating' => round((float) ($this->ratings_avg_rating ?? 0), 1),
            'ratings_count' => (int) ($this->approved_ratings_count ?? 0),

            // Sensitive business documents are only exposed to the store owner or an admin.
            'commercial_registry' => $this->when($isOwnerOrAdmin, $this->commercial_registry),
            'tax_number' => $this->when($isOwnerOrAdmin, $this->tax_number),
            'commercial_file_url' => $this->when(
                $isOwnerOrAdmin,
                fn () => $storage->temporaryPrivateUrl($this->commercial_file_path, 15)
            ),
            'rejection_reason' => $this->when($isOwnerOrAdmin, $this->rejection_reason),

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
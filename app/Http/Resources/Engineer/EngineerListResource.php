<?php

namespace App\Http\Resources\Engineer;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EngineerListResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $storage = app(\App\Services\SupabaseStorageService::class);

        return [
            'id' => $this->id,
            'name' => $this->user?->name,
            'email' => $this->user?->email,
            'phone' => $this->user?->phone,
            'governorate_id' => $this->user?->governorate_id,
            'governorate' => $this->user?->governorate?->name_ar ?? $this->user?->governorate?->name_en ?? $this->user?->governorate?->name,
            'governorate_name' => $this->user?->governorate?->name_ar ?? $this->user?->governorate?->name_en ?? $this->user?->governorate?->name,
            'location_coordinates' => $this->user?->default_coordinates,
            'license_number' => $this->license_number,
            'years_of_experience' => $this->years_of_experience,
            'bio' => $this->bio,
            'profile_photo_path' => $this->profile_photo_path,
            'cv_url' => $this->cv_path ? $storage->temporaryPrivateUrl($this->cv_path, 30) : null,
            'approval_status' => $this->approval_status,
            'approved_at' => $this->approved_at,
            'created_at' => $this->created_at,
            'specializations' => $this->specializations->map(function ($specialization) {
                return [
                    'id' => $specialization->id,
                    'name' => $specialization->name,
                ];
            }),
            'rating_average' => $this->ratings_avg_rating
                ? round((float) $this->ratings_avg_rating, 1)
                : null,

            'ratings_count' => $this->ratings_count,
        ];
    }
}

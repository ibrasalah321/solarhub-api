<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EngineerProfileResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,

            'user' => [
                'id' => $this->user?->id,
                'name' => $this->user?->name,
                'email' => $this->user?->email,
                'phone' => $this->user?->phone,
                'governorate_id' => $this->user?->governorate_id,
            ],

            'license_number' => $this->license_number,

            'years_of_experience' => $this->years_of_experience,

            'bio' => $this->bio,

            'profile_photo_path' => $this->profile_photo_path,

            'approval_status' => $this->approval_status,

            'specializations' => $this->specializations->map(function ($specialization) {
                return [
                    'id' => $specialization->id,
                    'name' => $specialization->name,
                ];
            }),

            'created_at' => $this->created_at,
        ];
    }
}
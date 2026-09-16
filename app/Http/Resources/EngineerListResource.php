<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EngineerListResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->user?->name,
            'governorate_id' => $this->user?->governorate_id,
            'years_of_experience' => $this->years_of_experience,
            'bio' => $this->bio,
            'profile_photo_path' => $this->profile_photo_path,
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
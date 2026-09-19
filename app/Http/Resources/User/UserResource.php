<?php

namespace App\Http\Resources\User;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'email_verified_at' => $this->email_verified_at,
            'status' => $this->status,
            'default_coordinates' => $this->default_coordinates,

            'governorate' => $this->whenLoaded('governorate', fn () => [
                'id' => $this->governorate?->id,
                'name_ar' => $this->governorate?->name_ar,
            ]),

            'has_store' => $this->whenLoaded('store', fn () => $this->store !== null),
            'has_engineer_profile' => $this->whenLoaded('engineerProfile', fn () => $this->engineerProfile !== null),

            'created_at' => $this->created_at,
        ];
    }
}

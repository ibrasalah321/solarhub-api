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
                'name_en' => $this->governorate?->name_en,
            ]),

            'roles' => $this->relationLoaded('roles') ? $this->roles->pluck('name')->values()->all() : [],
            'role' => $this->relationLoaded('roles') && $this->roles->isNotEmpty()
                ? $this->roles->first()->name
                : ($this->relationLoaded('store') && $this->store !== null
                    ? 'supplier'
                    : ($this->relationLoaded('engineerProfile') && $this->engineerProfile !== null
                        ? 'engineer'
                        : 'customer')),

            'has_store' => $this->relationLoaded('store') ? $this->store !== null: null,
            'has_engineer_profile' => $this->relationLoaded('engineerProfile') ? $this->engineerProfile !== null: null,
            'created_at' => $this->created_at,
        ];
    }
}

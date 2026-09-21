<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'status' => $this->status,

            'email_verified' => $this->email_verified_at !== null,
            'email_verified_at' => $this->email_verified_at,

            // NOTE: the `users` table has no `role` column yet. This key is
            // kept in the payload shape the client expects but is always
            // null until a role/permission system is added to the schema.
            'role' => null,

            'governorate' => $this->whenLoaded('governorate', fn () => [
                'id' => $this->governorate?->id,
                'name_ar' => $this->governorate?->name_ar,
            ]),

            'has_store' => $this->whenLoaded('store', fn () => $this->store !== null),
            'has_engineer_profile' => $this->whenLoaded('engineerProfile', fn () => $this->engineerProfile !== null),

            'onboarding_status' => $this->resolveOnboardingStatus(),

            'created_at' => $this->created_at,
        ];
    }

    private function resolveOnboardingStatus(): string
    {
        if ($this->email_verified_at === null) {
            return 'email_unverified';
        }

        if ($this->status !== 'active') {
            return $this->status;
        }

        return 'active';
    }
}
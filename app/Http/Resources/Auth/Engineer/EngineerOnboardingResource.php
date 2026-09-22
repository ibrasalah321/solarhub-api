<?php

namespace App\Http\Resources\Auth\Engineer;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EngineerOnboardingResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'has_cv' => (bool) $this->cv_path,
            'license_number' => $this->license_number,
            'approval_status' => $this->approval_status,
            'rejection_reason' => $this->rejection_reaon
        ];
    }
}

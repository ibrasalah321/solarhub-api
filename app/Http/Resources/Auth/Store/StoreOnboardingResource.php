<?php

namespace App\Http\Resources\Auth\Store;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StoreOnboardingResource extends JsonResource
{
    public function toArray(Request $request): array
    {

        return [
            'id' => $this->id,
            'company_name' => $this->company_name,
            'has_commercial_file' => (bool) $this->commercial_file_path     ,
            'approval_status' => $this->approval_status,
            'rejection_reason' => $this->rejection_reason,
            'approved_at' => $this->approved_at?->toISOString(),
            'created_at' => $this->created_at?->toISOString(),
        ];
    }
}
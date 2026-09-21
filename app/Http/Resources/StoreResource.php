<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StoreResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'company_name' => $this->company_name,
            'logo' => $this->company_logo_path,
            'whatsapp' => $this->whatsapp_number,
            'store_type' => $this->store_type,
            'address' => $this->address_details,
            'approval_status' => $this->approval_status,
            'is_approved' => $this->approval_status === 'approved',
            'owner' => [
                'id' => $this->user->id ?? null,
                'name' => $this->user->name ?? null,
                'email' => $this->user->email ?? null,
            ],
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
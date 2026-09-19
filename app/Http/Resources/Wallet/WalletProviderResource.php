<?php

namespace App\Http\Resources\Wallet;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class WalletProviderResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'logo_url' => $this->logo 
                ? Storage::disk('supabase_public')->url($this->logo) 
                : null,
            'instructions' => $this->instructions,
            'status' => $this->status,
        ];
    }
}
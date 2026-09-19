<?php

namespace App\Http\Resources\Wallet;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserWalletResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'account_name' => $this->account_name,
            'account_number' => $this->account_number,
            'is_default' => (bool) $this->is_default,
            'provider' => $this->whenLoaded('provider', fn() => [
                'id' => $this->provider->id,
                'name' => $this->provider->name,
            ]),
        ];
    }
}
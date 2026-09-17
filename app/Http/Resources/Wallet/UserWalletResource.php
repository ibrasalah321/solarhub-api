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
            'user_id' => $this->user_id,
            'wallet_provider_id' => $this->wallet_provider_id,
            'account_number' => $this->account_number,
            'account_name' => $this->account_name,
            'is_default' => (bool) $this->is_default,
            'user' => $this->whenLoaded('user', fn() => [
                'id' => $this->user->id,
                'name' => $this->user->name,
                'phone' => $this->user->phone,
            ]),
            'wallet_provider' => $this->whenLoaded('walletProvider', fn() => new WalletProviderResource($this->walletProvider)),
        ];
    }
}
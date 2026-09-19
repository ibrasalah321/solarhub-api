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

            'wallet_provider' => new WalletProviderResource(
                $this->whenLoaded('walletProvider')
            ),

            'account_number' => $this->account_number,
            'account_name' => $this->account_name,
            'is_default' => $this->is_default,
            'is_active' => $this->is_active,
            'created_at' => $this->created_at,
        ];
    }
}

<?php

namespace App\Http\Resources\Payment;

use App\Http\Resources\Wallet\WalletProviderResource;
use App\Services\SupabaseStorageService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderPaymentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'order_id' => $this->order_id,

            'wallet_provider' => new WalletProviderResource(
                $this->whenLoaded('walletProvider')
            ),

            'amount' => $this->amount,
            'transaction_reference' => $this->transaction_reference,

            // Receipt images live on the private disk; a short-lived signed URL is generated on demand.
            'receipt_url' => app(SupabaseStorageService::class)
                ->temporaryPrivateUrl($this->receipt_image, 15),

            'status' => $this->status,

            'verified_by' => $this->whenLoaded('verifiedBy', fn () => [
                'id' => $this->verifiedBy?->id,
                'name' => $this->verifiedBy?->name,
            ]),

            'paid_at' => $this->paid_at,
            'verified_at' => $this->verified_at,
            'created_at' => $this->created_at,
        ];
    }
}

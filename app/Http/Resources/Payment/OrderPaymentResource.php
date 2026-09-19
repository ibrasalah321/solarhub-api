<?php

namespace App\Http\Resources\Payment;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class OrderPaymentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'order_id' => $this->order_id,
            'amount' => (float) $this->amount,
            'transaction_reference' => $this->transaction_reference,
            'status' => $this->status,
            'receipt_url' => $this->receipt_image 
                ? Storage::disk('supabase_private')->temporaryUrl($this->receipt_image, now()->addMinutes(20))
                : null,
            'paid_at' => $this->paid_at?->toIso8601String(),
            'verified_at' => $this->verified_at?->toIso8601String(),
            'notes' => $this->notes,
            'provider' => $this->whenLoaded('walletProvider', fn() => [
                'id' => $this->walletProvider->id,
                'name' => $this->walletProvider->name,
            ]),
        ];
    }
}
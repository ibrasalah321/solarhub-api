<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreStorePayoutRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        $payoutId = $this->route('store_payout') ?? $this->route('id');

        return [
            'order_store_id' => 'required|exists:order_stores,id|unique:store_payouts,order_store_id,' . $payoutId,
            'user_wallet_id' => 'nullable|exists:user_wallets,id',
            'total_amount' => 'required|numeric|min:0',
            'commission_rate' => 'required|numeric|min:0',
            'commission_amount' => 'required|numeric|min:0',
            'net_amount' => 'required|numeric|min:0',
            'status' => 'required|in:pending,completed,failed',
            'transfer_reference' => 'nullable|string|max:100',
            'paid_at' => 'nullable|date',
        ];
    }
}
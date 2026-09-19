<?php

namespace App\Http\Requests\Wallet;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateUserWalletRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        $walletId = $this->route('user_wallet') ?? $this->route('id');

        return [
            'account_number' => [
                'sometimes',
                'required',
                'string',
                'max:50',
                Rule::unique('user_wallets')->ignore($walletId)->where(function ($query) {
                    return $query->where('user_id', $this->user()->id)
                        ->where('wallet_provider_id', $this->wallet_provider_id ?? $this->wallet?->wallet_provider_id);
                }),
            ],
            'account_name' => 'sometimes|required|string|max:150',
            'is_default' => 'sometimes|boolean',
        ];
    }
}
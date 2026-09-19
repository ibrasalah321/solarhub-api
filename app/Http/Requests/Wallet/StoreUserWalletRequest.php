<?php

namespace App\Http\Requests\Wallet;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreUserWalletRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'wallet_provider_id' => 'required|integer|exists:wallet_providers,id',
            'account_number' => [
                'required',
                'string',
                'max:50',
                Rule::unique('user_wallets')->where(function ($query) {
                    return $query->where('user_id', $this->user()->id)
                        ->where('wallet_provider_id', $this->wallet_provider_id);
                }),
            ],
            'account_name' => 'required|string|max:150',
            'is_default' => 'nullable|boolean',
        ];
    }
}
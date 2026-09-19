<?php

namespace App\Http\Requests\Wallet;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateUserWalletRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $userWallet = $this->route('userWallet');

        $walletProviderId = $this->input(
            'wallet_provider_id',
            $userWallet?->wallet_provider_id
        );

        return [
            'wallet_provider_id' => [
                'sometimes',
                'required',
                'integer',
                'exists:wallet_providers,id',
            ],

            'account_number' => [
                'sometimes',
                'required',
                'string',
                'max:100',
                Rule::unique('user_wallets', 'account_number')
                    ->where('user_id', $this->user()?->id)
                    ->where('wallet_provider_id', $walletProviderId)
                    ->ignore($userWallet),
            ],

            'account_name' => [
                'sometimes',
                'nullable',
                'string',
                'max:255',
            ],

            'is_default' => [
                'sometimes',
                'boolean',
            ],

            'is_active' => [
                'sometimes',
                'boolean',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'account_number.unique' => 'You already have this account number saved for the selected wallet provider.',
        ];
    }
}

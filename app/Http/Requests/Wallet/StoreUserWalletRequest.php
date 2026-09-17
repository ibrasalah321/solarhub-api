<?php

namespace App\Http\Requests\Wallet;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreUserWalletRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'wallet_provider_id' => [
                'required',
                'integer',
                'exists:wallet_providers,id',
            ],

            'account_number' => [
                'required',
                'string',
                'max:100',
                Rule::unique('user_wallets', 'account_number')
                    ->where('user_id', $this->user()?->id)
                    ->where('wallet_provider_id', $this->input('wallet_provider_id')),
            ],

            'account_name' => [
                'nullable',
                'string',
                'max:255',
            ],

            'is_default' => [
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

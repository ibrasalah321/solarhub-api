<?php

namespace App\Http\Requests\Wallet;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateWalletProviderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $walletProvider = $this->route('wallet_provider');

        return [
            'code' => [
                'sometimes',
                'required',
                'string',
                'max:50',
                Rule::unique('wallet_providers', 'code')->ignore($walletProvider),
            ],

            'name_ar' => [
                'sometimes',
                'required',
                'string',
                'max:100',
            ],

            'name_en' => [
                'sometimes',
                'nullable',
                'string',
                'max:100',
            ],

            'logo' => [
                'sometimes',
                'nullable',
                'file',
                'image',
                'max:2048',
            ],

            'is_active' => [
                'sometimes',
                'boolean',
            ],
        ];
    }
}

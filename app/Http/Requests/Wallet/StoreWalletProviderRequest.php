<?php

namespace App\Http\Requests\Wallet;

use Illuminate\Foundation\Http\FormRequest;

class StoreWalletProviderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'code' => [
                'required',
                'string',
                'max:50',
                'unique:wallet_providers,code',
            ],

            'name_ar' => [
                'required',
                'string',
                'max:100',
            ],

            'name_en' => [
                'nullable',
                'string',
                'max:100',
            ],

            'logo' => [
                'nullable',
                'file',
                'image',
                'max:2048',
            ],

            'is_active' => [
                'boolean',
            ],
        ];
    }
}

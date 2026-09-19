<?php

namespace App\Http\Requests\Wallet;

use Illuminate\Foundation\Http\FormRequest;

class StoreWalletProviderRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:100|unique:wallet_providers,name',
            'logo' => 'nullable|image|max:2048',
            'instructions' => 'nullable|string',
            'status' => 'required|in:active,inactive',
        ];
    }
}
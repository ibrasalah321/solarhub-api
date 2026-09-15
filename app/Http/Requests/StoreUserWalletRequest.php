<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreUserWalletRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'user_id' => 'required|exists:users,id',
            'wallet_provider_id' => 'required|exists:wallet_providers,id',
            'account_number' => 'required|string|max:100',
            'account_name' => 'nullable|string|max:255',
            'is_default' => 'boolean',
        ];
    }
}
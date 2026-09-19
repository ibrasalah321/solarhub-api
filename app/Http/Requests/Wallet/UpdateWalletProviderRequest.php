<?php

namespace App\Http\Requests\Wallet;

use Illuminate\Foundation\Http\FormRequest;

class UpdateWalletProviderRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        $providerId = $this->route('wallet_provider') ?? $this->route('id');

        return [
            'name' => 'sometimes|required|string|max:100|unique:wallet_providers,name,' . $providerId,
            'logo' => 'nullable|image|max:2048',
            'instructions' => 'nullable|string',
            'status' => 'sometimes|required|in:active,inactive',
        ];
    }
}
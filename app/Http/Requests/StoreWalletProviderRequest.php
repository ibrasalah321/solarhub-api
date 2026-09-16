<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreWalletProviderRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        $providerId = $this->route('wallet_provider') ?? $this->route('id');

        return [
            'code' => 'required|string|max:50|unique:wallet_providers,code,' . $providerId,
            'name_ar' => 'required|string|max:100',
            'name_en' => 'nullable|string|max:100',
            'logo_path' => 'nullable|string|max:255',
            'is_active' => 'boolean',
        ];
    }
}
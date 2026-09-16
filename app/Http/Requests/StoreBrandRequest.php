<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreBrandRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        $brandId = $this->route('brand') ?? $this->route('id');

        return [
            'name' => 'required|string|max:255|unique:brands,name,' . $brandId,
            'logo' => 'nullable|string|max:255',
            'website' => 'nullable|url|max:255',
            'is_active' => 'boolean',
        ];
    }
}
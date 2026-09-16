<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreGovernorateRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'name_ar' => 'required|string|max:100',
            'name_en' => 'nullable|string|max:100',
            'is_active' => 'boolean',
        ];
    }
}
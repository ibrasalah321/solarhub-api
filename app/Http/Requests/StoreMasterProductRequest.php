<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreMasterProductRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'category_id' => 'required|exists:categories,id',
            'brand_id' => 'required|exists:brands,id',
            'title' => 'required|string|max:255',
            'model_number' => 'nullable|string|max:100',
            'description' => 'nullable|string',
            'datasheet_file' => 'nullable|string|max:255',
            'is_active' => 'boolean',
        ];
    }
}
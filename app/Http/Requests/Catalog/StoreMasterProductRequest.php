<?php

namespace App\Http\Requests\Catalog;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreMasterProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'category_id' => [
                'required',
                'integer',
                'exists:categories,id',
            ],

            'brand_id' => [
                'required',
                'integer',
                'exists:brands,id',
            ],

            'title' => [
                'required',
                'string',
                'max:255',
            ],

            'model_number' => [
                'nullable',
                'string',
                'max:100',
                Rule::unique('master_products', 'model_number')
                    ->where('brand_id', $this->input('brand_id')),
            ],

            'description' => [
                'nullable',
                'string',
            ],

            'datasheet' => [
                'nullable',
                'file',
                'mimes:pdf',
                'max:10240',
            ],

            'is_active' => [
                'boolean',
            ],
        ];
    }
}

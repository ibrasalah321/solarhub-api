<?php

namespace App\Http\Requests\Catalog;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateMasterProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $brandId = $this->input('brand_id', $this->route('master_product')?->brand_id);

        return [
            'category_id' => [
                'sometimes',
                'required',
                'integer',
                'exists:categories,id',
            ],

            'brand_id' => [
                'sometimes',
                'required',
                'integer',
                'exists:brands,id',
            ],

            'title' => [
                'sometimes',
                'required',
                'string',
                'max:255',
            ],

            'model_number' => [
                'sometimes',
                'nullable',
                'string',
                'max:100',
                Rule::unique('master_products', 'model_number')
                    ->where('brand_id', $brandId)
                    ->ignore($this->route('master_product')),
            ],

            'description' => [
                'sometimes',
                'nullable',
                'string',
            ],

            'datasheet' => [
                'sometimes',
                'nullable',
                'file',
                'mimes:pdf',
                'max:10240',
            ],

            'is_active' => [
                'sometimes',
                'boolean',
            ],
        ];
    }
}

<?php

namespace App\Http\Requests\Catalog;

use Illuminate\Foundation\Http\FormRequest;

class StoreProductSpecificationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'product_id' => [
                'required',
                'integer',
                'exists:master_products,id',
            ],

            'name' => [
                'required',
                'string',
                'max:255',
            ],

            'value' => [
                'required',
                'string',
                'max:255',
            ],

            'unit' => [
                'nullable',
                'string',
                'max:50',
            ],
        ];
    }
}

<?php

namespace App\Http\Requests\Catalog;

use Illuminate\Foundation\Http\FormRequest;

class StoreProductImageRequest extends FormRequest
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

            'image' => [
                'required',
                'file',
                'image',
                'max:2048',
            ],

            'is_featured' => [
                'boolean',
            ],
        ];
    }
}

<?php

namespace App\Http\Requests\Store;

use Illuminate\Foundation\Http\FormRequest;

class StoreStoreProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'master_product_id' => [
                'required',
                'integer',
                'exists:master_products,id',
            ],

            'governorate_id' => [
                'nullable',
                'integer',
                'exists:governorates,id',
            ],

            'price' => [
                'nullable',
                'numeric',
                'min:0',
            ],

            'stock_quantity' => [
                'required',
                'integer',
                'min:0',
            ],

            'min_order_qty' => [
                'required',
                'integer',
                'min:1',
            ],

            'warranty_period' => [
                'nullable',
                'string',
                'max:100',
            ],
        ];
    }
}

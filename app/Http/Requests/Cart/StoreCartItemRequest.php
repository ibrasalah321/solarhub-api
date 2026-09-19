<?php

namespace App\Http\Requests\Cart;

use Illuminate\Foundation\Http\FormRequest;

class StoreCartItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'store_product_id' => [
                'required',
                'integer',
                'exists:store_products,id',
            ],

            'quantity' => [
                'required',
                'integer',
                'min:1',
            ],
        ];
    }
}

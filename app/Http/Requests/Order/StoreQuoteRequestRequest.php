<?php

namespace App\Http\Requests\Order;

use Illuminate\Foundation\Http\FormRequest;

class StoreQuoteRequestRequest extends FormRequest
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

            'customer_target_price' => [
                'nullable',
                'numeric',
                'min:0',
            ],

            'customer_notes' => [
                'nullable',
                'string',
                'max:1000',
            ],
        ];
    }
}

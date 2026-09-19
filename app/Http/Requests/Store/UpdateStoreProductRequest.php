<?php

namespace App\Http\Requests\Store;

use Illuminate\Foundation\Http\FormRequest;

class UpdateStoreProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'governorate_id' => [
                'sometimes',
                'nullable',
                'integer',
                'exists:governorates,id',
            ],

            'price' => [
                'sometimes',
                'nullable',
                'numeric',
                'min:0',
            ],

            'stock_quantity' => [
                'sometimes',
                'required',
                'integer',
                'min:0',
            ],

            'min_order_qty' => [
                'sometimes',
                'required',
                'integer',
                'min:1',
            ],

            'warranty_period' => [
                'sometimes',
                'nullable',
                'string',
                'max:100',
            ],

            // Manual override; automatic active/out_of_stock derivation still applies based on stock_quantity.
            'status' => [
                'sometimes',
                'required',
                'string',
                'in:active,inactive,out_of_stock',
            ],
        ];
    }
}

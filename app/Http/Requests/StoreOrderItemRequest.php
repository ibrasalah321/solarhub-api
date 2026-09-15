<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreOrderItemRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'order_store_id' => 'required|exists:order_stores,id',
            'store_product_id' => 'required|exists:store_products,id',
            'unit_price' => 'required|numeric|min:0',
            'quantity' => 'required|integer|min:1',
            'total_price' => 'required|numeric|min:0',
        ];
    }
}
<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCartItemRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'cart_id' => 'required|exists:carts,id',
            'store_product_id' => 'required|exists:store_products,id',
            'quantity' => 'required|integer|min:1',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'cart_id' => 'السلة',
            'store_product_id' => 'المنتج المعروض',
            'quantity' => 'الكمية',
        ];
    }
}
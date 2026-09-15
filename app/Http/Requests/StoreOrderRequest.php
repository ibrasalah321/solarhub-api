<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreOrderRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'order_number' => 'required|string|unique:orders,order_number|max:100',
            'customer_id' => 'required|exists:users,id',
            'delivery_governorate_id' => 'nullable|exists:governorates,id',
            'total_amount' => 'required|numeric|min:0',
            'delivery_address' => 'required|string|max:500',
            'delivery_coordinates' => 'nullable',
            'payment_method' => 'required|in:cash_on_delivery,bank_transfer,wallet',
            'status' => 'required|in:pending,processing,completed,cancelled',
        ];
    }
}
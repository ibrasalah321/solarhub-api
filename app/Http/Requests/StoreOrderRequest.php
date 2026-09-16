<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreOrderRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        $orderId = $this->route('order') ?? $this->route('id');

        return [
            'order_number' => 'required|string|max:100|unique:orders,order_number,' . $orderId,
            'customer_id' => 'required|exists:users,id',
            'delivery_governorate_id' => 'nullable|exists:governorates,id',
            'total_amount' => 'required|numeric|min:0',
            'delivery_address' => 'required|string|max:500',
            'status' => 'required|in:pending,processing,completed,cancelled',
            'delivery_coordinates' => 'nullable',
        ];
    }
}
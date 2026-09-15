<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreOrderPaymentRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'order_id' => 'required|exists:orders,id',
            'payment_method' => 'required|string|max:100',
            'amount' => 'required|numeric|min:0',
            'receipt_image' => 'required|string|max:255',
            'status' => 'nullable|string|max:50',
            'verified_by' => 'nullable|exists:users,id',
        ];
    }
}
<?php

namespace App\Http\Requests\Payment;

use Illuminate\Foundation\Http\FormRequest;

class StoreOrderPaymentRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'order_id' => 'required|integer|exists:orders,id',
            'wallet_provider_id' => 'required|integer|exists:wallet_providers,id',
            'amount' => 'required|numeric|min:0.01',
            'transaction_reference' => 'required|string|max:100',
            'receipt_image' => 'required|image|mimes:jpeg,png,jpg,webp|max:5120',
        ];
    }
}
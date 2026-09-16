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
            'wallet_provider_id' => 'nullable|exists:wallet_providers,id',
            'amount' => 'required|numeric|min:0',
            'transaction_reference' => 'nullable|string|max:100',
            'receipt_image' => 'nullable|string|max:255',
            'status' => 'required|in:pending,verified,rejected',
            'verified_by' => 'nullable|exists:users,id',
            'paid_at' => 'nullable|date',
            'verified_at' => 'nullable|date',
        ];
    }
}
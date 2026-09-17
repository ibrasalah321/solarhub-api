<?php

namespace App\Http\Requests\Payment;

use Illuminate\Foundation\Http\FormRequest;

class StoreOrderPaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'order_id' => [
                'required',
                'integer',
                'exists:orders,id',
            ],

            'wallet_provider_id' => [
                'nullable',
                'integer',
                'exists:wallet_providers,id',
            ],

            'amount' => [
                'required',
                'numeric',
                'min:0.01',
            ],

            'transaction_reference' => [
                'nullable',
                'string',
                'max:150',
            ],

            'receipt' => [
                'required',
                'file',
                'mimes:jpg,jpeg,png,pdf',
                'max:5120',
            ],

            'paid_at' => [
                'nullable',
                'date',
            ],
        ];
    }
}

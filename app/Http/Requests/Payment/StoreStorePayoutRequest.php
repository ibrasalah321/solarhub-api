<?php

namespace App\Http\Requests\Payment;

use Illuminate\Foundation\Http\FormRequest;

class StoreStorePayoutRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'order_store_id' => [
                'required',
                'integer',
                'exists:order_stores,id',
                'unique:store_payouts,order_store_id',
            ],

            'user_wallet_id' => [
                'nullable',
                'integer',
                'exists:user_wallets,id',
            ],

            // Percentage value, e.g. 10 for 10%.
            'commission_rate' => [
                'required',
                'numeric',
                'min:0',
                'max:100',
            ],

            'transfer_reference' => [
                'nullable',
                'string',
                'max:150',
            ],
        ];
    }
}

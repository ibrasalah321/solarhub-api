<?php

namespace App\Http\Requests\Payment;

use Illuminate\Foundation\Http\FormRequest;

class StoreStorePayoutRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'store_id' => 'required|integer|exists:stores,id',
            'commission_percentage' => 'nullable|numeric|between:0,100',
        ];
    }
}
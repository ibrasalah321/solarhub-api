<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreStorePayoutRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'order_id' => 'required|exists:orders,id',
            'store_id' => 'required|exists:stores,id',
            'total_amount' => 'required|numeric|min:0',
            'platform_commission' => 'required|numeric|min:0',
            'net_amount' => 'required|numeric|min:0',
            'transfer_status' => 'nullable|string|max:50',
            'transfer_reference' => 'nullable|string|max:255',
        ];
    }
}
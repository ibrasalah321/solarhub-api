<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreOrderStoreRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'order_id' => 'required|exists:orders,id',
            'store_id' => 'required|exists:stores,id',
            'subtotal' => 'required|numeric|min:0',
            'status' => 'required|in:pending,accepted,shipped,delivered,rejected',
            'notes' => 'nullable|string',
        ];
    }
}
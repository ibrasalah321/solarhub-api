<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreOrderQuotationRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'order_store_id' => 'required|exists:order_stores,id',
            'total_price' => 'required|numeric|min:0',
            'notes' => 'nullable|string',
            'status' => 'nullable|string|max:50',
        ];
    }
}
<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreQuoteRequestRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'customer_id' => 'required|exists:users,id',
            'store_product_id' => 'required|exists:store_products,id',
            'quantity' => 'required|integer|min:1',
            'customer_target_price' => 'nullable|numeric|min:0',
            'offered_unit_price' => 'nullable|numeric|min:0',
            'total_price' => 'nullable|numeric|min:0',
            'status' => 'nullable|in:pending,responded,accepted,rejected,cancelled',
            'customer_notes' => 'nullable|string',
            'store_notes' => 'nullable|string',
            'responded_at' => 'nullable|date',
            'accepted_at' => 'nullable|date',
        ];
    }
}
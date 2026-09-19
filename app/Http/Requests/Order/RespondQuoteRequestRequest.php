<?php

namespace App\Http\Requests\Order;

use Illuminate\Foundation\Http\FormRequest;

class RespondQuoteRequestRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'offered_unit_price' => [
                'required',
                'numeric',
                'min:0.01',
            ],

            'store_notes' => [
                'nullable',
                'string',
                'max:1000',
            ],
        ];
    }
}

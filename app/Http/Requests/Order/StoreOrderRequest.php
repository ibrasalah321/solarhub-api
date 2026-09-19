<?php

namespace App\Http\Requests\Order;

use Illuminate\Foundation\Http\FormRequest;

class StoreOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'delivery_governorate_id' => [
                'nullable',
                'integer',
                'exists:governorates,id',
            ],

            'delivery_address' => [
                'required',
                'string',
                'max:500',
            ],

            'delivery_coordinates' => [
                'nullable',
                'string',
                'max:255',
            ],
        ];
    }
}

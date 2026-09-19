<?php

namespace App\Http\Requests\Order;

use Illuminate\Foundation\Http\FormRequest;

class UpdateOrderStoreStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'status' => [
                'required',
                'string',
                'in:accepted,rejected,shipped,delivered,cancelled',
            ],

            'notes' => [
                'nullable',
                'string',
            ],
        ];
    }
}

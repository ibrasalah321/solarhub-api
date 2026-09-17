<?php

namespace App\Http\Requests\Payment;

use Illuminate\Foundation\Http\FormRequest;

class UpdateStorePayoutRequest extends FormRequest
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
                'in:completed,failed',
            ],

            'transfer_reference' => [
                'nullable',
                'string',
                'max:150',
            ],
        ];
    }
}

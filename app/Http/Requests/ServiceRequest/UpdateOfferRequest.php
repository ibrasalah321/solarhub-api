<?php

namespace App\Http\Requests\ServiceRequest;

use Illuminate\Foundation\Http\FormRequest;

class UpdateOfferRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'proposed_cost' => [
                'sometimes',
                'required',
                'numeric',
                'min:0',
            ],

            'execution_time_days' => [
                'sometimes',
                'required',
                'integer',
                'min:1',
            ],

            'technical_proposal' => [
                'sometimes',
                'required',
                'string',
            ],

            'proposal' => [
                'sometimes',
                'nullable',
                'file',
                'mimes:pdf',
                'max:10240',
            ],
        ];
    }
}
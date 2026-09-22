<?php

namespace App\Http\Requests\ServiceRequest;

use Illuminate\Foundation\Http\FormRequest;

class StoreOfferRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'proposed_cost' => [
                'required',
                'numeric',
                'min:0',
            ],

            'execution_time_days' => [
                'required',
                'integer',
                'min:1',
            ],

            'technical_proposal' => [
                'required',
                'string',
            ],

            'proposal' => [
                'nullable',
                'file',
                'mimes:pdf',
                'max:10240',
            ],
        ];
    }
}
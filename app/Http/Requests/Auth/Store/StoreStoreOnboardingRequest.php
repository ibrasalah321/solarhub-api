<?php

namespace App\Http\Requests\Auth\Store;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreStoreOnboardingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'company_name' => [
                'required',
                'string',
                'max:150',
            ],

            'commercial_registry' => [
                'nullable',
                'string',
                'max:100',
            ],

            'commercial_file' => [
                'nullable',
                'file',
                'mimes:pdf,jpg,jpeg,png',
                'max:5120',
            ],
            'address_details' => ['required' , 'string'],
            'store_type' => [
                'required',
                Rule::in([
                    'wholesaler',
                    'retailer',
                    'authorized_agent',
                ]),
            ],
        ];
    }
}
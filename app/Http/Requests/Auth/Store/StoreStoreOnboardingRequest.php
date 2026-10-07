<?php

namespace App\Http\Requests\Auth\Store;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreStoreOnboardingRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        foreach ([
            'company_name',
            'commercial_registry',
            'address_details',
            'store_type',
        ] as $field) {
            if (is_string($this->input($field))) {
                $this->merge([
                    $field => trim($this->input($field)),
                ]);
            }
        }
    }

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
                'required',
                'string',
                'max:100',
            ],

            'commercial_file' => [
                'required',
                'file',
                'mimes:pdf,jpg,jpeg,png',
                'max:5120',
            ],

            'address_details' => [
                'required',
                'string',
            ],

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

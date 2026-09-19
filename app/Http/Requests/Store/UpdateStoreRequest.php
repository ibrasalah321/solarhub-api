<?php

namespace App\Http\Requests\Store;

use Illuminate\Foundation\Http\FormRequest;

class UpdateStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'company_name' => [
                'sometimes',
                'required',
                'string',
                'max:255',
            ],

            'commercial_registry' => [
                'sometimes',
                'nullable',
                'string',
                'max:100',
            ],

            'commercial_file' => [
                'sometimes',
                'nullable',
                'file',
                'mimes:pdf,jpg,jpeg,png',
                'max:5120',
            ],

            'tax_number' => [
                'sometimes',
                'nullable',
                'string',
                'max:100',
            ],

            'bio' => [
                'sometimes',
                'nullable',
                'string',
            ],

            'company_logo' => [
                'sometimes',
                'nullable',
                'file',
                'image',
                'max:2048',
            ],

            'whatsapp_number' => [
                'sometimes',
                'nullable',
                'string',
                'max:30',
            ],

            'store_type' => [
                'sometimes',
                'required',
                'string',
                'in:wholesaler,retailer,authorized_agent',
            ],

            'address_details' => [
                'sometimes',
                'required',
                'string',
            ],

            'location_coordinates' => [
                'sometimes',
                'nullable',
                'string',
                'max:255',
            ],
        ];
    }
}

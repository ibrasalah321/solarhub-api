<?php

namespace App\Http\Requests\Store;

use Illuminate\Foundation\Http\FormRequest;

class StoreStoreRequest extends FormRequest
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
                'max:255',
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

            'tax_number' => [
                'nullable',
                'string',
                'max:100',
            ],

            'bio' => [
                'nullable',
                'string',
            ],

            'company_logo' => [
                'nullable',
                'file',
                'image',
                'max:2048',
            ],

            'whatsapp_number' => [
                'nullable',
                'string',
                'max:30',
            ],

            'store_type' => [
                'required',
                'string',
                'in:wholesaler,retailer,authorized_agent',
            ],

            'address_details' => [
                'required',
                'string',
            ],

            'location_coordinates' => [
                'nullable',
                'string',
                'max:255',
            ],
        ];
    }
}

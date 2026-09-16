<?php

namespace App\Http\Requests\ServiceRequest;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreServiceRequestRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'service_type_id' => [
                'required',
                'integer',
                Rule::exists('service_types', 'id')
                    ->where('is_active', true),
            ],

            'governorate_id' => [
                'nullable',
                'integer',
                'exists:governorates,id',
            ],

            'system_capacity_estimate' => [
                'nullable',
                'numeric',
                'min:0',
            ],

            'location_details' => [
                'nullable',
                'string',
            ],

            'location_coordinates' => [
                'nullable',
                'string',
                'max:255',
            ],

            'description' => [
                'required',
                'string',
            ],

            'attachment' => [
                'nullable',
                'file',
                'mimes:pdf,jpg,jpeg,png,webp',
                'max:10240',
            ],
        ];
    }
}
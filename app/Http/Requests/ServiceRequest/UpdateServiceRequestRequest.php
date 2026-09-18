<?php

namespace App\Http\Requests\ServiceRequest;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateServiceRequestRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'service_type_id' => [
                'sometimes',
                'required',
                'integer',
                Rule::exists('service_types', 'id')
                    ->where('is_active', true),
            ],

            'governorate_id' => [
                'sometimes',
                'nullable',
                'integer',
                'exists:governorates,id',
            ],

            'system_capacity_estimate' => [
                'sometimes',
                'nullable',
                'numeric',
                'min:0',
            ],

            'location_details' => [
                'sometimes',
                'nullable',
                'string',
            ],

            'location_coordinates' => [
                'sometimes',
                'nullable',
                'string',
                'max:255',
            ],

            'description' => [
                'sometimes',
                'required',
                'string',
            ],

            'attachment' => [
                'sometimes',
                'nullable',
                'file',
                'mimes:pdf,jpg,jpeg,png,webp',
                'max:10240',
            ],
        ];
    }
}
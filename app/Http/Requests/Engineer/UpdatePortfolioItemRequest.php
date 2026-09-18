<?php

namespace App\Http\Requests\Engineer;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePortfolioItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'project_title' => [
                'sometimes',
                'required',
                'string',
                'max:255',
            ],

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

            'system_capacity' => [
                'sometimes',
                'nullable',
                'numeric',
                'min:0',
            ],

            'description' => [
                'sometimes',
                'nullable',
                'string',
            ],

            'address_text' => [
                'sometimes',
                'nullable',
                'string',
                'max:255',
            ],

            'location_coordinates' => [
                'sometimes',
                'nullable',
                'string',
                'max:255',
            ],

            'image' => [
                'sometimes',
                'nullable',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:5120',
            ],

            'file' => [
                'sometimes',
                'nullable',
                'file',
                'mimes:pdf',
                'max:10240',
            ],
        ];
    }
}
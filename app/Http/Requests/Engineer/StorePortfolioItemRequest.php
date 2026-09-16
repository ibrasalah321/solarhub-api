<?php

namespace App\Http\Requests\Engineer;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePortfolioItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'project_title' => [
                'required',
                'string',
                'max:255',
            ],

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

            'system_capacity' => [
                'nullable',
                'numeric',
                'min:0',
            ],

            'description' => [
                'nullable',
                'string',
            ],

            'address_text' => [
                'nullable',
                'string',
                'max:255',
            ],

            'location_coordinates' => [
                'nullable',
                'string',
                'max:255',
            ],

            'image' => [
                'nullable',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:5120',
            ],

            'file' => [
                'nullable',
                'file',
                'mimes:pdf',
                'max:10240',
            ],
        ];
    }
}
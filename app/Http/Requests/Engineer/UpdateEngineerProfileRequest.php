<?php

namespace App\Http\Requests\Engineer;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
class UpdateEngineerProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'license_number' => [
                'sometimes',
                'nullable',
                'string',
                'max:100',
            ],

            'years_of_experience' => [
                'sometimes',
                'nullable',
                'integer',
                'min:0',
            ],

            'bio' => [
                'sometimes',
                'nullable',
                'string',
            ],

            'profile_photo' => [
                'sometimes',
                'nullable',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:2048',
            ],

            'cv' => [
                'sometimes',
                'nullable',
                'file',
                'mimes:pdf',
                'max:5120',
            ],

            'specialization_ids' => [
                'sometimes',
                'array',
            ],

            'specialization_ids.*' => [
                'integer',
                'distinct',
                Rule::exists('specializations', 'id')
                    ->where('is_active', true),
            ],
        ];
    }
}
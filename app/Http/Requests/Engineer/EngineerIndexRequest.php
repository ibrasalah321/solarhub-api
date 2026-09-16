<?php

namespace App\Http\Requests\Engineer;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class EngineerIndexRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'search' => [
                'sometimes',
                'nullable',
                'string',
                'max:150',
            ],

            'specialization_id' => [
                'sometimes',
                'nullable',
                'integer',
                Rule::exists('specializations', 'id')
                    ->where('is_active', true),
            ],

            'governorate_id' => [
                'sometimes',
                'nullable',
                'integer',
                'exists:governorates,id',
            ],

            'per_page' => [
                'sometimes',
                'integer',
                'min:1',
                'max:50',
            ],
        ];
    }
}
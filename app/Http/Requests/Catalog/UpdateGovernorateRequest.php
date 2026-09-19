<?php

namespace App\Http\Requests\Catalog;

use Illuminate\Foundation\Http\FormRequest;

class UpdateGovernorateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name_ar' => [
                'sometimes',
                'required',
                'string',
                'max:100',
            ],

            'name_en' => [
                'sometimes',
                'nullable',
                'string',
                'max:100',
            ],

            'is_active' => [
                'sometimes',
                'boolean',
            ],
        ];
    }
}

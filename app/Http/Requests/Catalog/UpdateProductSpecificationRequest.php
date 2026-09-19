<?php

namespace App\Http\Requests\Catalog;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProductSpecificationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => [
                'sometimes',
                'required',
                'string',
                'max:255',
            ],

            'value' => [
                'sometimes',
                'required',
                'string',
                'max:255',
            ],

            'unit' => [
                'sometimes',
                'nullable',
                'string',
                'max:50',
            ],
        ];
    }
}

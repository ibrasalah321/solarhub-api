<?php

namespace App\Http\Requests\Catalog;

use Illuminate\Foundation\Http\FormRequest;

class StoreBrandRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => [
                'required',
                'string',
                'max:255',
                'unique:brands,name',
            ],

            'logo' => [
                'nullable',
                'file',
                'image',
                'max:2048',
            ],

            'website' => [
                'nullable',
                'url',
                'max:255',
            ],

            'is_active' => [
                'boolean',
            ],
        ];
    }
}

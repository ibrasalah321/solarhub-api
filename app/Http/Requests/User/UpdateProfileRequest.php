<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'governorate_id' => [
                'sometimes',
                'nullable',
                'integer',
                'exists:governorates,id',
            ],

            'name' => [
                'sometimes',
                'required',
                'string',
                'max:150',
            ],

            'phone' => [
                'sometimes',
                'required',
                'string',
                'max:30',
                Rule::unique('users', 'phone')->ignore($this->user()?->id),
            ],

            'default_coordinates' => [
                'sometimes',
                'nullable',
                'string',
                'max:255',
            ],

            'current_password' => [
                'required_with:password',
                'string',
            ],

            'password' => [
                'sometimes',
                'string',
                'min:8',
                'confirmed',
            ],
        ];
    }
}

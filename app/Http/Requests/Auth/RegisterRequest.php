<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class RegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'name' => is_string($this->name)
                ? trim($this->name)
                : $this->name,

            'email' => is_string($this->email)
                ? mb_strtolower(trim($this->email))
                : $this->email,

            'phone' => is_string($this->phone)
                ? trim($this->phone)
                : $this->phone,

            'role' => is_string($this->role)
                ? mb_strtolower(trim($this->role))
                : $this->role,
        ]);
    }

    public function rules(): array
    {
        return [
            'name' => [
                'required',
                'string',
                'max:150',
            ],

            'email' => [
                'required',
                'string',
                'email:rfc',
                'max:150',
                'unique:users,email',
            ],

            'phone' => [
                'required',
                'string',
                'regex:/^\+?[0-9]{7,20}$/',
                'unique:users,phone',
            ],

            'password' => [
                'required',
                'confirmed',
                Password::min(8)
                    ->letters()
                    ->mixedCase()
                    ->numbers(),
            ],

            'role' => [
                'required',
                'string',
                Rule::in([
                    'customer',
                    'supplier',
                    'engineer',
                ]),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'phone.regex' => 'The phone number must contain between 7 and 20 digits.',
            'role.in' => 'The selected role is not allowed for public registration.',
        ];
    }
}

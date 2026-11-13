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
        $role = $this->role;
        if (empty($role)) {
            $path = $this->path();
            if (str_contains($path, 'customer')) {
                $role = 'customer';
            } elseif (str_contains($path, 'engineer')) {
                $role = 'engineer';
            } elseif (str_contains($path, 'supplier')) {
                $role = 'supplier';
            } else {
                $role = 'customer';
            }
        }

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

            'role' => is_string($role)
                ? mb_strtolower(trim($role))
                : 'customer',
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
                Rule::unique('users', 'email')->where(function ($query) {
                    return $query->whereNotNull('email_verified_at')->whereNull('deleted_at');
                }),
            ],

            'phone' => [
                'required',
                'string',
                'regex:/^\+?[0-9]{7,20}$/',
                Rule::unique('users', 'phone')->where(function ($query) {
                    return $query->whereNotNull('email_verified_at')->whereNull('deleted_at');
                }),
            ],

            'password' => [
                'required',
                'string',
                'confirmed',
                Password::min(8)
                    ->letters()
                    ->mixedCase()
                    ->numbers(),
            ],

            'password_confirmation' => [
                'required',
                'string',
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

            'governorate' => ['nullable', 'string', 'max:100'],
            'governorate_id' => ['nullable', 'integer'],
            'city' => ['nullable', 'string', 'max:100'],
            'address_details' => ['nullable', 'string'],
            'default_coordinates' => ['nullable', new \App\Rules\GeographyPoint()],
            'location_coordinates' => ['nullable', new \App\Rules\GeographyPoint()],
            'company_name' => ['nullable', 'string', 'max:150'],
            'license_number' => ['nullable', 'string', 'max:100'],
            'commercial_registry' => ['nullable', 'string', 'max:100'],
            'store_type' => ['nullable', 'string', Rule::in(['wholesaler', 'retailer', 'authorized_agent'])],
            'cv' => ['nullable', 'file', 'max:10240'],
            'cvFile' => ['nullable', 'file', 'max:10240'],
            'commercial_file' => ['nullable', 'file', 'max:10240'],
            'commercialRegisterFile' => ['nullable', 'file', 'max:10240'],
            'governorate_id' => ['nullable', 'integer', 'exists:governorates,id'],
            'governorate' => ['nullable', 'string', 'max:100'],
            'default_coordinates' => ['nullable', new \App\Rules\GeographyPoint()],
            'location_coordinates' => ['nullable', new \App\Rules\GeographyPoint()],
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

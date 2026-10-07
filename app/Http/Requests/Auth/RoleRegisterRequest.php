<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class RoleRegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        foreach ([
            'name',
            'email',
            'phone',
            'license_number',
            'company_name',
            'commercial_registry',
            'address_details',
            'store_type',
        ] as $field) {
            if (! is_string($this->input($field))) {
                continue;
            }

            $value = trim($this->input($field));

            if ($field === 'email' || $field === 'store_type') {
                $value = mb_strtolower($value);
            }

            $this->merge([$field => $value]);
        }
    }

    public function rules(): array
    {
        $rules = [
            'name' => ['required', 'string', 'max:150'],
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
                'string',
                'confirmed',
                Password::min(8)
                    ->letters()
                    ->mixedCase()
                    ->numbers(),
            ],
            'password_confirmation' => ['required', 'string'],
            'role' => ['prohibited'],
        ];

        if ($this->route('accountRole') === 'engineer') {
            $rules += [
                'license_number' => ['required', 'string', 'max:100'],
                'cv' => [
                    'required',
                    'file',
                    'mimes:pdf,doc,docx',
                    'max:5120',
                ],
            ];
        }

        if ($this->route('accountRole') === 'supplier') {
            $rules += [
                'company_name' => ['required', 'string', 'max:150'],
                'commercial_registry' => [
                    'required',
                    'string',
                    'max:100',
                ],
                'commercial_file' => [
                    'required',
                    'file',
                    'mimes:pdf,jpg,jpeg,png',
                    'max:5120',
                ],
                'address_details' => ['required', 'string'],
                'store_type' => [
                    'required',
                    'string',
                    Rule::in([
                        'wholesaler',
                        'retailer',
                        'authorized_agent',
                    ]),
                ],
            ];
        }

        return $rules;
    }

    public function messages(): array
    {
        return [
            'phone.regex' => 'The phone number must contain between 7 and 20 digits.',
            'role.prohibited' => 'The account role is fixed by the registration endpoint.',
        ];
    }
}

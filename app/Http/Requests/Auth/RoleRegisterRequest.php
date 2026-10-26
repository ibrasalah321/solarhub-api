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
            'governorate',
            'default_coordinates',
            'location_coordinates',
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

            // Common geographical and address fields
            'governorate_id' => ['nullable', 'integer', 'exists:governorates,id'],
            'governorate' => ['nullable', 'string', 'max:100'],
            'default_coordinates' => ['nullable', 'string', 'max:255'],
            'location_coordinates' => ['nullable', 'string', 'max:255'],
            'address_details' => ['nullable', 'string', 'max:500'],
        ];

        if ($this->route('accountRole') === 'engineer') {
            $rules += [
                'license_number' => ['required', 'string', 'max:100'],
                'cv' => [
                    'required',
                    'file',
                    'mimes:pdf',
                    'max:10240',
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
                    'mimes:pdf,jpg,jpeg,png,webp',
                    'max:10240',
                ],
                'address_details' => ['required', 'string', 'max:500'],
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
            'cv.required' => 'يرجى إرفاق ملف السيرة الذاتية بصيغة PDF.',
            'cv.mimes' => 'يجب أن يكون ملف السيرة الذاتية للمهندس بصيغة PDF فقط.',
            'cv.max' => 'حجم ملف السيرة الذاتية يجب ألا يتجاوز 10 ميجابايت.',
            'commercial_file.required' => 'يرجى رفع ملف أو صورة السجل التجاري.',
            'commercial_file.mimes' => 'يجب أن يكون ملف السجل التجاري بصيغة PDF أو صورة (JPG, PNG, WEBP).',
            'commercial_file.max' => 'حجم ملف السجل التجاري يجب ألا يتجاوز 10 ميجابايت.',
        ];
    }
}

<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class VerifyOtpRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'email' => is_string($this->email)
                ? mb_strtolower(trim($this->email))
                : $this->email,

            'code' => is_string($this->code)
                ? trim($this->code)
                : $this->code,
        ]);
    }

    public function rules(): array
    {
        return [
            'email' => [
                'bail',
                'required',
                'string',
                'email:rfc',
                'max:150',
            ],

            'code' => [
                'bail',
                'required',
                'string',
                'size:6',
                'regex:/^[0-9]{6}$/',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'code.size' => 'The verification code must be exactly 6 digits.',
            'code.regex' => 'The verification code must contain digits only.',
        ];
    }
}

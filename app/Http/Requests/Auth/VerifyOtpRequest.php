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
        $otpLength = $this->otpLength();

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
                'size:'.$otpLength,
                'regex:'.sprintf(
                    '/^[0-9]{%d}$/',
                    $otpLength
                ),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'code.size' => sprintf(
                'The verification code must be exactly %d digits.',
                $this->otpLength()
            ),

            'code.regex' => sprintf(
                'The verification code must contain exactly %d digits.',
                $this->otpLength()
            ),
        ];
    }

    private function otpLength(): int
    {
        return min(
            8,
            max(
                4,
                (int) config('verification.otp.length', 6)
            )
        );
    }
}

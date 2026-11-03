<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class RoleLoginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if (is_string($this->login)) {
            $login = trim($this->login);

            $this->merge([
                'login' => str_contains($login, '@')
                    ? mb_strtolower($login)
                    : $login,
            ]);
        }
    }

    public function rules(): array
    {
        return [
            'login' => ['required', 'string'],
            'password' => ['required', 'string'],
            'role' => ['prohibited'],
        ];
    }

    public function messages(): array
    {
        return [
            'role.prohibited' => 'The account role is fixed by the login endpoint.',
        ];
    }
}

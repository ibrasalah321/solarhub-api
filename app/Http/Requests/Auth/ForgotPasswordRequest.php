<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class ForgotPasswordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            // Intentionally no `exists:users,email` rule here: validating
            // existence would let a caller enumerate registered accounts
            // through the validation error response.
            'email' => [
                'required',
                'email',
            ],
        ];
    }
}

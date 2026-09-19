<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreEmailOtpRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'user_id' => 'required|exists:users,id',
            'email' => 'required|email|max:150',
            'otp' => 'required|string|max:255',
            'attempts' => 'nullable|integer|min:0',
            'expires_at' => 'required|date',
            'verified_at' => 'nullable|date',
        ];
    }
}
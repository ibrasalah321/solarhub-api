<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreUserRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        $userId = $this->route('user') ?? $this->route('id');

        return [
            'governorate_id' => 'nullable|exists:governorates,id',
            'name' => 'required|string|max:150',
            'email' => 'required|email|max:150|unique:users,email,' . $userId,
            'phone' => 'required|string|max:30|unique:users,phone,' . $userId,
            'password' => $this->isMethod('post') ? 'required|string|min:6' : 'nullable|string|min:6',
            'status' => 'required|in:active,inactive,suspended',
            'default_coordinates' => 'nullable|string',
        ];
    }
}
<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePlatformSettingRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'platform_name' => 'required|string|max:150',
            'support_phone' => 'required|string|max:50',
            'support_email' => 'required|email|max:150',
            'logo_path' => 'required|string|max:255',
            'favicon_path' => 'nullable|string|max:255',
            'currency' => 'required|string|max:10',
            'is_maintenance_mode' => 'boolean',
        ];
    }
}
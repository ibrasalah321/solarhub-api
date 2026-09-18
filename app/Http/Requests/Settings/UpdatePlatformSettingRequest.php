<?php

namespace App\Http\Requests\Settings;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePlatformSettingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'platform_name' => [
                'sometimes',
                'required',
                'string',
                'max:150',
            ],

            'support_phone' => [
                'sometimes',
                'required',
                'string',
                'max:50',
            ],

            'support_email' => [
                'sometimes',
                'required',
                'email',
                'max:150',
            ],

            'logo' => [
                'sometimes',
                'nullable',
                'file',
                'image',
                'max:2048',
            ],

            'favicon' => [
                'sometimes',
                'nullable',
                'file',
                'image',
                'max:512',
            ],

            'currency' => [
                'sometimes',
                'required',
                'string',
                'max:10',
            ],

            'is_maintenance_mode' => [
                'sometimes',
                'boolean',
            ],
        ];
    }
}

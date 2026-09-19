<?php

namespace App\Http\Requests\Notification;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateNotificationTemplateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'code' => [
                'sometimes',
                'required',
                'string',
                'max:100',
                Rule::unique('notification_templates', 'code')->ignore($this->route('notificationTemplate')),
            ],

            'title' => [
                'sometimes',
                'required',
                'string',
                'max:255',
            ],

            'body' => [
                'sometimes',
                'required',
                'string',
            ],

            'type' => [
                'sometimes',
                'required',
                'string',
                'max:50',
            ],

            'variables' => [
                'sometimes',
                'nullable',
                'array',
            ],

            'variables.*' => [
                'string',
            ],

            'is_active' => [
                'sometimes',
                'boolean',
            ],
        ];
    }
}

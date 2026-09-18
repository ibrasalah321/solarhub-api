<?php

namespace App\Http\Requests\Notification;

use Illuminate\Foundation\Http\FormRequest;

class StoreNotificationTemplateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'code' => [
                'required',
                'string',
                'max:100',
                'unique:notification_templates,code',
            ],

            'title' => [
                'required',
                'string',
                'max:255',
            ],

            'body' => [
                'required',
                'string',
            ],

            'type' => [
                'required',
                'string',
                'max:50',
            ],

            // Placeholder names usable in title/body, e.g. ["customer_name", "order_number"].
            'variables' => [
                'nullable',
                'array',
            ],

            'variables.*' => [
                'string',
            ],

            'is_active' => [
                'boolean',
            ],
        ];
    }
}

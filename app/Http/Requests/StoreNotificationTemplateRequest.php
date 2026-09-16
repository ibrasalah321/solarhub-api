<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreNotificationTemplateRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        $templateId = $this->route('notification_template') ?? $this->route('id');

        return [
            'code' => 'required|string|max:100|unique:notification_templates,code,' . $templateId,
            'title' => 'required|string|max:255',
            'body' => 'required|string',
            'type' => 'required|string|max:50',
            'variables' => 'nullable|array',
            'is_active' => 'boolean',
        ];
    }
}
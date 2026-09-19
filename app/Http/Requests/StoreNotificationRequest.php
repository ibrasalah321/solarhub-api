<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreNotificationRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        $notificationId = $this->route('notification') ?? $this->route('id');

        return [
            'id' => 'required|uuid|unique:notifications,id,' . $notificationId,
            'type' => 'required|string|max:255',
            'notifiable_type' => 'required|string|max:255',
            'notifiable_id' => 'required|integer',
            'data' => 'required|string',
            'read_at' => 'nullable|date',
        ];
    }
}
<?php

namespace App\Http\Resources\Notification;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class NotificationTemplateResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'code' => $this->code,
            'title' => $this->title,
            'body' => $this->body,
            'type' => $this->type,
            'variables' => $this->variables,
            'is_active' => $this->is_active,
            'created_at' => $this->created_at,
        ];
    }
}

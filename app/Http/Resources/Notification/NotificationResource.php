<?php

namespace App\Http\Resources\Notification;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class NotificationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $data = $this->data ?? [];

        return [
            'id' => $this->id,
            'event_id' => $this->event_id,
            'template_code' => $this->template_code,
            'type' => $this->type,
            'title' => $data['title'] ?? null,
            'body' => $data['body'] ?? null,
            'action_url' => $data['action_url'] ?? null,
            'sender' => $this->senderPayload(),
            'recipient_count' => isset($this->recipient_count)
                ? (int) $this->recipient_count
                : 1,
            'is_read' => $this->read_at !== null,
            'read_at' => $this->read_at,
            'created_at' => $this->created_at,
        ];
    }

    private function senderPayload(): array
    {
        if ($this->sender_type === 'system') {
            return ['type' => 'system', 'name' => 'النظام'];
        }

        if ($this->sender_type === 'legacy_unknown') {
            return ['type' => 'legacy_unknown', 'name' => 'مرسل غير معروف (سجل قديم)'];
        }

        return [
            'type' => 'user',
            'id' => $this->sender_id,
            'name' => $this->sender?->name,
        ];
    }
}

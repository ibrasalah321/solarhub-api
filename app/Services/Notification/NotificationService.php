<?php

namespace App\Services\Notification;

use App\Models\Notification;
use App\Models\NotificationTemplate;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class NotificationService
{
    public function inbox(
        User $user,
        bool $unreadOnly = false,
        ?string $type = null,
        int $perPage = 20
    ): LengthAwarePaginator {
        return Notification::query()
            ->where('notifiable_type', User::class)
            ->where('notifiable_id', $user->id)
            ->where(function ($query) use ($user) {
                $query->whereNull('sender_id')
                    ->orWhere('sender_id', '!=', $user->id);
            })
            ->when($unreadOnly, fn ($query) => $query->whereNull('read_at'))
            ->when($type, fn ($query) => $query->where('type', $type))
            ->with('sender:id,name')
            ->latest()
            ->paginate(min(max($perPage, 1), 100));
    }

    public function outbox(
        User $user,
        ?string $type = null,
        int $perPage = 20
    ): LengthAwarePaginator {
        $representativeIds = Notification::query()
            ->selectRaw('MIN(id)')
            ->where('sender_id', $user->id)
            ->when($type, fn ($query) => $query->where('type', $type))
            ->groupBy('event_id');

        return Notification::query()
            ->where('sender_id', $user->id)
            ->whereIn('id', $representativeIds)
            ->when($type, fn ($query) => $query->where('type', $type))
            ->select('notifications.*')
            ->selectSub(
                Notification::query()
                    ->selectRaw('COUNT(*)')
                    ->whereColumn('event_id', 'notifications.event_id'),
                'recipient_count'
            )
            ->with('sender:id,name')
            ->latest()
            ->paginate(min(max($perPage, 1), 100));
    }

    public function markAsRead(User $user, Notification $notification): Notification
    {
        abort_unless(
            $notification->notifiable_type === User::class
                && (int) $notification->notifiable_id === $user->id,
            403,
            'You are not allowed to manage this notification.'
        );

        if ($notification->read_at === null) {
            $notification->update(['read_at' => now()]);
        }

        return $notification->refresh()->load('sender:id,name');
    }

    public function markAllAsRead(User $user): void
    {
        Notification::query()
            ->where('notifiable_type', User::class)
            ->where('notifiable_id', $user->id)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);
    }

    /**
     * @param  iterable<User>  $recipients
     * @param  array<string, scalar|null>  $placeholders
     */
    public function sendToUsers(
        iterable $recipients,
        string $templateCode,
        array $placeholders,
        string $eventKey,
        ?User $sender = null,
        ?string $actionUrl = null,
        bool $systemGenerated = false
    ): Collection {
        $template = NotificationTemplate::query()
            ->where('code', $templateCode)
            ->where('is_active', true)
            ->firstOrFail();

        $this->validateVariables($template, $placeholders);

        $eventId = (string) Str::uuid();
        $created = new Collection;

        foreach (collect($recipients)->unique('id') as $recipient) {
            $dedupeKey = hash('sha256', implode('|', [
                User::class,
                $recipient->id,
                $templateCode,
                $eventKey,
            ]));

            $notification = Notification::query()->firstOrCreate(
                ['dedupe_key' => $dedupeKey],
                [
                    'type' => $template->type,
                    'notifiable_type' => User::class,
                    'notifiable_id' => $recipient->id,
                    'sender_id' => $sender?->id,
                    'sender_type' => $sender
                        ? 'user'
                        : ($systemGenerated ? 'system' : 'legacy_unknown'),
                    'template_code' => $templateCode,
                    'event_id' => $eventId,
                    'event_key' => $eventKey,
                    'data' => [
                        'title' => $this->render($template->title, $placeholders),
                        'body' => $this->render($template->body, $placeholders),
                        'action_url' => $actionUrl,
                        'variables' => $placeholders,
                    ],
                    'read_at' => null,
                ]
            );

            if ($notification->wasRecentlyCreated) {
                $created->push($notification);
            }
        }

        return $created;
    }

    /**
     * @param  iterable<User>  $recipients
     * @param  array<string, scalar|null>  $placeholders
     */
    public function sendAfterCommit(
        iterable $recipients,
        string $templateCode,
        array $placeholders,
        string $eventKey,
        ?User $sender = null,
        ?string $actionUrl = null,
        bool $systemGenerated = false
    ): void {
        if (! NotificationTemplate::query()
            ->where('code', $templateCode)
            ->where('is_active', true)
            ->exists()) {
            return;
        }

        $recipientIds = collect($recipients)->pluck('id')->unique()->values()->all();
        $senderId = $sender?->id;

        DB::afterCommit(function () use (
            $recipientIds,
            $templateCode,
            $placeholders,
            $eventKey,
            $senderId,
            $actionUrl,
            $systemGenerated
        ): void {
            $this->sendToUsers(
                User::query()->whereIn('id', $recipientIds)->get(),
                $templateCode,
                $placeholders,
                $eventKey,
                $senderId ? User::query()->find($senderId) : null,
                $actionUrl,
                $systemGenerated
            );
        });
    }

    /**
     * @param  array<string, scalar|null>  $placeholders
     */
    private function validateVariables(
        NotificationTemplate $template,
        array $placeholders
    ): void {
        $missing = array_diff($template->variables ?? [], array_keys($placeholders));

        if ($missing !== []) {
            throw ValidationException::withMessages([
                'notification_template' => [
                    'Missing template variables: '.implode(', ', $missing),
                ],
            ]);
        }
    }

    /**
     * @param  array<string, scalar|null>  $placeholders
     */
    private function render(string $text, array $placeholders): string
    {
        foreach ($placeholders as $key => $value) {
            $text = str_replace(
                '{{'.$key.'}}',
                (string) ($value ?? ''),
                $text
            );
        }

        return $text;
    }
}

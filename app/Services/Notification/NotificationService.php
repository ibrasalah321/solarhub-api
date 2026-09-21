<?php

namespace App\Services\Notification;

use App\Models\Notification;
use App\Models\NotificationTemplate;
use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;

class NotificationService
{
    /**
     * List notifications for the authenticated user, optionally unread only.
     */
    public function getForUser(User $user, bool $unreadOnly = false): LengthAwarePaginator
    {
        return $user->notifications()
            ->when($unreadOnly, fn ($query) => $query->whereNull('read_at'))
            ->latest()
            ->paginate(20);
    }

    /**
     * Mark a single notification as read.
     */
    public function markAsRead(User $user, Notification $notification): Notification
    {
        abort_unless(
            $notification->user_id === $user->id,
            403,
            'You are not allowed to manage this notification.'
        );

        if ($notification->read_at === null) {
            $notification->update(['read_at' => now()]);
        }

        return $notification;
    }

    /**
     * Mark all of the authenticated user's notifications as read.
     */
    public function markAllAsRead(User $user): void
    {
        $user->notifications()
            ->whereNull('read_at')
            ->update(['read_at' => now()]);
    }

    /**
     * Render an active template and create a notification for a user.
     * This is the internal entry point other services (orders, payments, ...)
     * should call to notify a user, rather than inserting into `notifications` directly.
     *
     * @param  array<string, string>  $placeholders  e.g. ['order_number' => 'SH-000123']
     */
            public function sendToUser(
            User $user,
            string $templateCode,
            array $placeholders = [],
            ?string $actionUrl = null
        ): Notification {
            $template = NotificationTemplate::query()
                ->where('code', $templateCode)
                ->where('is_active', true)
                ->firstOrFail();

            return Notification::create([
                'user_id'    => $user->id,
                'title'      => $this->render($template->title, $placeholders),
                'body'       => $this->render($template->body, $placeholders),
                'action_url' => $actionUrl,
            ]);
        }

    /**
     * Replace {{placeholder}} tokens in a template string.
     *
     * @param  array<string, string>  $placeholders
     */
    private function render(string $text, array $placeholders): string
    {
        foreach ($placeholders as $key => $value) {
            $text = str_replace('{{' . $key . '}}', (string) $value, $text);
        }

        return $text;
    }
}

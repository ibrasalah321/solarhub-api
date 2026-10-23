<?php

namespace App\Http\Controllers\Api\Notification;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use App\Models\User;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AdminNotificationController extends Controller
{
    use ApiResponseTrait;

    /**
     * Get aggregated system notifications for admin overview.
     */
    public function index(Request $request)
    {
        $representativeIds = Notification::query()
            ->selectRaw('MIN(id::text)::uuid as id')
            ->groupBy('event_id');

        $notifications = Notification::query()
            ->whereIn('id', $representativeIds)
            ->with(['notifiable', 'sender'])
            ->select('notifications.*')
            ->selectSub(
                Notification::query()
                    ->selectRaw('COUNT(*)')
                    ->whereColumn('event_id', 'notifications.event_id'),
                'total_recipients'
            )
            ->selectSub(
                Notification::query()
                    ->selectRaw('COUNT(read_at)')
                    ->whereColumn('event_id', 'notifications.event_id'),
                'read_recipients'
            )
            ->latest('created_at')
            ->get();

        $items = $notifications->map(function ($notification) {
            $data = is_array($notification->data) ? $notification->data : [];
            $total = (int) ($notification->total_recipients ?? 1);
            $read = (int) ($notification->read_recipients ?? 0);

            // Determine audience label
            $audience = $data['audience'] ?? null;
            if (!$audience) {
                $template = $notification->template_code ?? '';
                $text = ($data['body'] ?? '') . ' ' . ($data['title'] ?? '');
                if (str_contains($template, 'engineer') || str_contains($text, 'مهندس')) {
                    $audience = 'engineers';
                } elseif (str_contains($template, 'store') || str_contains($template, 'supplier') || str_contains($text, 'تاجر') || str_contains($text, 'متجر') || str_contains($text, 'مورد')) {
                    $audience = 'suppliers';
                } elseif (str_contains($template, 'customer') || str_contains($text, 'عميل')) {
                    $audience = 'customers';
                } else {
                    $audience = 'all';
                }
            }

            return [
                'id' => $notification->event_id ?: $notification->id,
                'title' => $data['title'] ?? 'إشعار من النظام',
                'content' => $data['body'] ?? $data['content'] ?? '',
                'audience' => $audience,
                'read_percentage' => $total > 0 ? (int) round(($read / $total) * 100) : null,
                'total_recipients' => $total,
                'read_recipients' => $read,
                'created_at' => $notification->created_at ? $notification->created_at->toISOString() : null,
            ];
        });

        return $this->successResponse($items, 'Admin notifications retrieved successfully.');
    }

    /**
     * Broadcast a notification to targeted platform users.
     */
    public function broadcast(Request $request)
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'min:3', 'max:150'],
            'content' => ['required', 'string', 'min:10', 'max:2000'],
            'audience' => ['required', 'string', 'in:all,customers,suppliers,engineers'],
        ]);

        $query = User::query()->where('is_active', true);
        if ($validated['audience'] === 'customers') {
            $query->role('customer');
        } elseif ($validated['audience'] === 'suppliers') {
            $query->role('supplier');
        } elseif ($validated['audience'] === 'engineers') {
            $query->role('engineer');
        }

        $recipients = $query->get();

        if ($recipients->isEmpty()) {
            $fallbackQuery = User::query();
            if ($validated['audience'] === 'customers') {
                $fallbackQuery->role('customer');
            } elseif ($validated['audience'] === 'suppliers') {
                $fallbackQuery->role('supplier');
            } elseif ($validated['audience'] === 'engineers') {
                $fallbackQuery->role('engineer');
            }
            $recipients = $fallbackQuery->get();
        }

        $eventId = (string) Str::uuid();
        $senderId = $request->user()?->id;

        foreach ($recipients as $recipient) {
            Notification::create([
                'type' => 'broadcast',
                'notifiable_type' => User::class,
                'notifiable_id' => $recipient->id,
                'sender_id' => $senderId,
                'sender_type' => $senderId ? 'user' : 'system',
                'template_code' => null,
                'event_id' => $eventId,
                'event_key' => 'broadcast:' . $eventId,
                'dedupe_key' => hash('sha256', implode('|', [User::class, $recipient->id, 'broadcast', $eventId])),
                'data' => [
                    'title' => $validated['title'],
                    'body' => $validated['content'],
                    'audience' => $validated['audience'],
                    'action_url' => null,
                ],
                'read_at' => null,
            ]);
        }

        return $this->successResponse([
            'id' => $eventId,
            'title' => $validated['title'],
            'content' => $validated['content'],
            'audience' => $validated['audience'],
            'read_percentage' => 0,
            'created_at' => now()->toISOString(),
            'recipients_count' => $recipients->count(),
        ], 'تم إرسال الإشعار العام بنجاح.');
    }
}

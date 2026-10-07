<?php

namespace App\Http\Controllers\Api\Notification;

use App\Http\Controllers\Controller;
use App\Http\Resources\Notification\NotificationResource;
use App\Models\Notification;
use App\Services\Notification\NotificationService;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    use ApiResponseTrait;

    public function __construct(
        private readonly NotificationService $notificationService
    ) {}

    public function index(Request $request)
    {
        return $this->inbox($request);
    }

    public function inbox(Request $request)
    {
        $notifications = $this->notificationService->inbox(
            $request->user(),
            $request->boolean('unread'),
            $request->string('type')->toString() ?: null,
            $request->integer('per_page', 20)
        );

        return $this->successResponse(
            NotificationResource::collection($notifications),
            'Incoming notifications retrieved successfully.'
        );
    }

    public function outbox(Request $request)
    {
        $notifications = $this->notificationService->outbox(
            $request->user(),
            $request->string('type')->toString() ?: null,
            $request->integer('per_page', 20)
        );

        return $this->successResponse(
            NotificationResource::collection($notifications),
            'Sent notifications retrieved successfully.'
        );
    }

    public function markAsRead(Request $request, Notification $notification)
    {
        $notification = $this->notificationService->markAsRead(
            $request->user(),
            $notification
        );

        return $this->successResponse(
            new NotificationResource($notification),
            'Notification marked as read.'
        );
    }

    public function markAllAsRead(Request $request)
    {
        $this->notificationService->markAllAsRead($request->user());

        return $this->successResponse(
            null,
            'All notifications marked as read.'
        );
    }
}

<?php

namespace App\Http\Controllers\Api;

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
    ) {
    }

    /**
     * Display the authenticated user's notifications.
     * Pass ?unread=1 to list unread notifications only.
     */
    public function index(Request $request)
    {
        $notifications = $this->notificationService->getForUser(
            $request->user(),
            $request->boolean('unread')
        );

        return $this->successResponse(
            NotificationResource::collection($notifications),
            'Notifications retrieved successfully.'
        );
    }

    /**
     * Mark a single notification as read.
     */
    public function markAsRead(Request $request, Notification $notification)
    {
        $notification = $this->notificationService->markAsRead($request->user(), $notification);

        return $this->successResponse(
            new NotificationResource($notification),
            'Notification marked as read.'
        );
    }

    /**
     * Mark all of the authenticated user's notifications as read.
     */
    public function markAllAsRead(Request $request)
    {
        $this->notificationService->markAllAsRead($request->user());

        return $this->successResponse(null, 'All notifications marked as read.');
    }
}

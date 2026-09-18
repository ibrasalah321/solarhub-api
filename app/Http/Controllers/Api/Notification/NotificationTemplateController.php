<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Notification\StoreNotificationTemplateRequest;
use App\Http\Requests\Notification\UpdateNotificationTemplateRequest;
use App\Http\Resources\Notification\NotificationTemplateResource;
use App\Models\NotificationTemplate;
use App\Traits\ApiResponseTrait;

class NotificationTemplateController extends Controller
{
    use ApiResponseTrait;

    /**
     * NOTE: intended for platform administrators. See the module-level note
     * regarding the current absence of a role/permission system.
     */
    public function index()
    {
        $templates = NotificationTemplate::query()->latest()->get();

        return $this->successResponse(
            NotificationTemplateResource::collection($templates),
            'Notification templates retrieved successfully.'
        );
    }

    public function show(NotificationTemplate $notificationTemplate)
    {
        return $this->successResponse(
            new NotificationTemplateResource($notificationTemplate),
            'Notification template retrieved successfully.'
        );
    }

    public function store(StoreNotificationTemplateRequest $request)
    {
        $template = NotificationTemplate::create($request->validated());

        return $this->successResponse(
            new NotificationTemplateResource($template),
            'Notification template created successfully.',
            201
        );
    }

    public function update(UpdateNotificationTemplateRequest $request, NotificationTemplate $notificationTemplate)
    {
        $notificationTemplate->update($request->validated());

        return $this->successResponse(
            new NotificationTemplateResource($notificationTemplate),
            'Notification template updated successfully.'
        );
    }

    public function destroy(NotificationTemplate $notificationTemplate)
    {
        $notificationTemplate->delete();

        return $this->successResponse(null, 'Notification template deleted successfully.');
    }
}

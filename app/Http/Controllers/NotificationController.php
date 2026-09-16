<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreNotificationRequest;
use App\Models\Notification;
use Illuminate\Http\JsonResponse;

class NotificationController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(Notification::latest()->paginate(20));
    }

    public function store(StoreNotificationRequest $request): JsonResponse
    {
        $notification = Notification::create($request->validated());
        return response()->json($notification, 201);
    }

    public function show($id): JsonResponse
    {
        $notification = Notification::findOrFail($id);
        return response()->json($notification);
    }

    public function update(StoreNotificationRequest $request, $id): JsonResponse
    {
        $notification = Notification::findOrFail($id);
        $notification->update($request->validated());
        return response()->json($notification);
    }

    public function destroy($id): JsonResponse
    {
        Notification::findOrFail($id)->delete();
        return response()->json(['message' => 'Notification deleted successfully']);
    }
}
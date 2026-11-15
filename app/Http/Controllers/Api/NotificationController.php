<?php

namespace App\\Http\\Controllers\\Api;

use App\\Http\\Controllers\\Controller;
use App\\Http\\Resources\\NotificationResource;
use Illuminate\\Http\\JsonResponse;
use Illuminate\\Http\\Request;
use Illuminate\\Http\\Resources\\Json\\AnonymousResourceCollection;

class NotificationController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $validated = $request->validate([
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:100'],
        ]);

        $notifications = $request->user()
            ->notifications()
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->paginate($validated['per_page'] ?? 15)
            ->withQueryString();

        return NotificationResource::collection($notifications);
    }

    public function markAsRead(Request $request, string $notification): NotificationResource
    {
        $notification = $request->user()
            ->notifications()
            ->whereKey($notification)
            ->firstOrFail();

        $notification->markAsRead();

        return new NotificationResource($notification->refresh());
    }

    public function markAllAsRead(Request $request): JsonResponse
    {
        $updated = $request->user()
            ->unreadNotifications()
            ->update([
                'read_at' => now(),
                'updated_at' => now(),
            ]);

        return response()->json([
            'data' => [
                'updated' => $updated,
            ],
        ]);
    }
}

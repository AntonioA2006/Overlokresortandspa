<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\UserNotification;
use App\Services\NotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index(Request $request, NotificationService $notificationService): JsonResponse
    {
        $this->authorize('viewAny', UserNotification::class);

        return response()->json([
            'unread_count' => $notificationService->unreadCount($request->user()),
            'notifications' => $notificationService->unreadForUser($request->user())->map(
                fn (UserNotification $notification): array => $this->serialize($notification),
            )->values(),
        ]);
    }

    public function markAsRead(
        UserNotification $notification,
        NotificationService $notificationService,
    ): JsonResponse {
        $this->authorize('markAsRead', $notification);

        $notificationService->markAsRead($notification);

        return response()->json([
            'notification' => $this->serialize($notification->refresh()),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function serialize(UserNotification $notification): array
    {
        return [
            'id' => $notification->id,
            'type' => $notification->type->value,
            'title' => $notification->title,
            'message' => $notification->message,
            'read_at' => $notification->read_at?->toIso8601String(),
            'sent_at' => $notification->sent_at?->toIso8601String(),
            'reservation_id' => $notification->reservation_id,
            'action_url' => $notification->actionUrl($request->user()),
        ];
    }
}

<?php

namespace App\Http\Controllers\Guest;

use App\Http\Controllers\Controller;
use App\Models\UserNotification;
use App\Services\NotificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class NotificationController extends Controller
{
    public function index(Request $request, NotificationService $notificationService): View
    {
        $this->authorize('viewAny', UserNotification::class);

        return view('guest.notifications.index', [
            'notifications' => $notificationService->paginateForUser($request->user()),
            'unreadCount' => $notificationService->unreadCount($request->user()),
        ]);
    }

    public function markAsRead(
        Request $request,
        UserNotification $notification,
        NotificationService $notificationService,
    ): RedirectResponse {
        $this->authorize('markAsRead', $notification);

        $notificationService->markAsRead($notification);

        return back()->with('status', __('notifications.marked_read'));
    }

    public function markAllAsRead(Request $request, NotificationService $notificationService): RedirectResponse
    {
        $this->authorize('viewAny', UserNotification::class);

        $notificationService->markAllAsRead($request->user());

        return back()->with('status', __('notifications.all_marked_read'));
    }
}

<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Conversation;
use App\Models\Reservation;
use App\Models\Room;
use App\Models\User;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        return view('admin.dashboard', [
            'hotelName' => config('overlook.hotel_name'),
            'stats' => [
                'users' => User::query()->count(),
                'rooms' => Room::query()->count(),
                'reservations' => Reservation::query()->count(),
                'open_conversations' => Conversation::query()->where('status', '!=', 'closed')->count(),
            ],
            'recentReservations' => Reservation::query()
                ->with(['user', 'room.roomType'])
                ->latest()
                ->limit(8)
                ->get(),
            'recentAuditLogs' => AuditLog::query()
                ->with('user')
                ->latest('created_at')
                ->limit(8)
                ->get(),
        ]);
    }
}

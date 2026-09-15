<?php

namespace App\Services;

use App\Enums\NotificationType;
use App\Models\Reservation;
use App\Models\User;
use App\Models\UserNotification;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class NotificationService
{
    public function sendRoomDeliveryReminder(Reservation $reservation, int $intervalHours): UserNotification
    {
        $checkInTime = config('overlook.default_check_in_time', '15:00');
        $dedupeKey = app(RoomDeliveryReminderService::class)->dedupeKey($reservation, $intervalHours);

        return UserNotification::query()->firstOrCreate(
            ['dedupe_key' => $dedupeKey],
            [
                'user_id' => $reservation->user_id,
                'reservation_id' => $reservation->id,
                'type' => NotificationType::RoomDeliveryReminder,
                'title' => __('notifications.room_delivery_title'),
                'message' => __('notifications.room_delivery_body', [
                    'hours' => $intervalHours,
                    'time' => $checkInTime,
                ]),
                'data' => [
                    'interval_hours' => $intervalHours,
                    'check_in_time' => $checkInTime,
                ],
                'sent_at' => now(),
            ]
        );
    }

    public function notifyReservationConfirmed(Reservation $reservation): UserNotification
    {
        return UserNotification::query()->firstOrCreate(
            ['dedupe_key' => 'reservation_confirmed:'.$reservation->id],
            [
                'user_id' => $reservation->user_id,
                'reservation_id' => $reservation->id,
                'type' => NotificationType::ReservationConfirmed,
                'title' => __('notifications.reservation_confirmed_title'),
                'message' => __('notifications.reservation_confirmed_body', [
                    'code' => $reservation->code,
                ]),
                'data' => ['code' => $reservation->code],
                'sent_at' => now(),
            ]
        );
    }

    public function notifyReservationCancelled(Reservation $reservation): UserNotification
    {
        return UserNotification::query()->firstOrCreate(
            ['dedupe_key' => 'reservation_cancelled:'.$reservation->id],
            [
                'user_id' => $reservation->user_id,
                'reservation_id' => $reservation->id,
                'type' => NotificationType::ReservationCancelled,
                'title' => __('notifications.reservation_cancelled_title'),
                'message' => __('notifications.reservation_cancelled_body', [
                    'code' => $reservation->code,
                ]),
                'data' => ['code' => $reservation->code],
                'sent_at' => now(),
            ]
        );
    }

    public function notifyRoomDelivered(Reservation $reservation): UserNotification
    {
        return UserNotification::query()->firstOrCreate(
            ['dedupe_key' => 'room_delivered:'.$reservation->id],
            [
                'user_id' => $reservation->user_id,
                'reservation_id' => $reservation->id,
                'type' => NotificationType::RoomDelivered,
                'title' => __('notifications.room_delivered_title'),
                'message' => __('notifications.room_delivered_body', [
                    'code' => $reservation->code,
                ]),
                'data' => ['code' => $reservation->code],
                'sent_at' => now(),
            ]
        );
    }

    public function notifySupportMessage(User $recipient, int $conversationId, string $preview): UserNotification
    {
        return UserNotification::query()->create([
            'user_id' => $recipient->id,
            'reservation_id' => null,
            'type' => NotificationType::SupportMessage,
            'title' => __('notifications.support_message_title'),
            'message' => $preview,
            'dedupe_key' => 'support_message:'.Str::uuid(),
            'data' => ['conversation_id' => $conversationId],
            'sent_at' => now(),
        ]);
    }

    /**
     * @return Paginator<int, UserNotification>
     */
    public function paginateForUser(User $user, int $perPage = 20): Paginator
    {
        return UserNotification::query()
            ->whereBelongsTo($user)
            ->with('reservation')
            ->orderByDesc('sent_at')
            ->orderByDesc('id')
            ->simplePaginate($perPage);
    }

    public function unreadCount(User $user): int
    {
        return UserNotification::query()
            ->whereBelongsTo($user)
            ->whereNull('read_at')
            ->count();
    }

    /**
     * @return Collection<int, UserNotification>
     */
    public function unreadForUser(User $user, int $limit = 10): Collection
    {
        return UserNotification::query()
            ->whereBelongsTo($user)
            ->whereNull('read_at')
            ->orderByDesc('sent_at')
            ->orderByDesc('id')
            ->limit($limit)
            ->get();
    }

    public function markAsRead(UserNotification $notification): UserNotification
    {
        $notification->markAsRead();

        return $notification->refresh();
    }

    public function markAllAsRead(User $user): int
    {
        return UserNotification::query()
            ->whereBelongsTo($user)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);
    }
}

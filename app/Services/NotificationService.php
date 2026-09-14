<?php

namespace App\Services;

use App\Enums\NotificationType;
use App\Models\Reservation;
use App\Models\UserNotification;

class NotificationService
{
    public function sendRoomDeliveryReminder(Reservation $reservation, int $intervalHours): UserNotification
    {
        $checkInTime = config('overlook.default_check_in_time', '15:00');
        $dedupeKey = app(RoomDeliveryReminderService::class)->dedupeKey($reservation, $intervalHours);

        return UserNotification::firstOrCreate(
            ['dedupe_key' => $dedupeKey],
            [
                'user_id' => $reservation->user_id,
                'reservation_id' => $reservation->id,
                'type' => NotificationType::RoomDeliveryReminder,
                'title' => 'Tu habitación estará lista pronto',
                'message' => sprintf(
                    'Faltan %d hora(s) para la entrega de tu habitación. Hora estimada: %s.',
                    $intervalHours,
                    $checkInTime
                ),
                'data' => [
                    'interval_hours' => $intervalHours,
                    'check_in_time' => $checkInTime,
                ],
                'sent_at' => now(),
            ]
        );
    }
}

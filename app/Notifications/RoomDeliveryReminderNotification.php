<?php

namespace App\Notifications;

use App\Models\Reservation;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class RoomDeliveryReminderNotification extends Notification
{
    public function __construct(
        public Reservation $reservation,
        public int $intervalHours,
    ) {}

    /**
     * @return list<string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $this->reservation->loadMissing(['room.roomType']);

        $hotelName = (string) config('overlook.hotel_name');
        $checkInTime = (string) config('overlook.default_check_in_time', '15:00');

        return (new MailMessage)
            ->subject(__('mail.room_delivery_subject', [
                'hotel' => $hotelName,
                'code' => $this->reservation->code,
            ]))
            ->markdown('mail.stay.room-delivery-reminder', [
                'url' => url(route('guest.reservations.show', $this->reservation, false)),
                'userName' => $notifiable->name,
                'hotelName' => $hotelName,
                'code' => $this->reservation->code,
                'roomName' => $this->reservation->room?->roomType?->name ?: __('mail.room_fallback'),
                'hours' => $this->intervalHours,
                'time' => $checkInTime,
                'checkIn' => $this->reservation->check_in_date?->toDateString(),
            ]);
    }
}

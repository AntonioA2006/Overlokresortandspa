<?php

namespace App\Notifications;

use App\Models\Reservation;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ReservationConfirmedNotification extends Notification
{
    public function __construct(public Reservation $reservation) {}

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

        return (new MailMessage)
            ->subject(__('mail.reservation_confirmed_subject', [
                'hotel' => $hotelName,
                'code' => $this->reservation->code,
            ]))
            ->markdown('mail.stay.reservation-confirmed', $this->stayPayload($notifiable, $hotelName));
    }

    /**
     * @return array<string, mixed>
     */
    private function stayPayload(object $notifiable, string $hotelName): array
    {
        return [
            'url' => url(route('guest.reservations.show', $this->reservation, false)),
            'userName' => $notifiable->name,
            'hotelName' => $hotelName,
            'code' => $this->reservation->code,
            'roomName' => $this->reservation->room?->roomType?->name ?: __('mail.room_fallback'),
            'checkIn' => $this->reservation->check_in_date?->toDateString(),
            'checkOut' => $this->reservation->check_out_date?->toDateString(),
        ];
    }
}

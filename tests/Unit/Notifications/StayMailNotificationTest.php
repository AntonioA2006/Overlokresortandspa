<?php

namespace Tests\Unit\Notifications;

use App\Models\Reservation;
use App\Models\Room;
use App\Models\RoomType;
use App\Models\User;
use App\Notifications\ReservationCancelledNotification;
use App\Notifications\ReservationConfirmedNotification;
use App\Notifications\RoomDeliveryReminderNotification;
use App\Notifications\VerifyEmailNotification;
use Tests\TestCase;

class StayMailNotificationTest extends TestCase
{
    public function test_confirmation_mail_includes_hotel_branding_and_reservation_code(): void
    {
        $user = User::factory()->make([
            'name' => 'Ana Huésped',
            'email' => 'ana@overlook.test',
        ]);
        $reservation = $this->makeReservation($user, 'OVL-MAIL01', 'Garden Suite');

        $html = (new ReservationConfirmedNotification($reservation))->toMail($user)->render();

        $this->assertStringContainsString('Overlook Resort &amp; Spa', $html);
        $this->assertStringContainsString('Ana Huésped', $html);
        $this->assertStringContainsString('OVL-MAIL01', $html);
        $this->assertStringContainsString('Garden Suite', $html);
        $this->assertStringContainsString('/guest/reservations/'.$reservation->id, $html);
        $this->assertStringContainsString('Tu reservación está confirmada', $html);
    }

    public function test_confirmation_mail_escapes_dangerous_guest_names(): void
    {
        $user = User::factory()->make([
            'name' => "Ana <script>alert('xss')</script>",
            'email' => 'ana@overlook.test',
        ]);
        $reservation = $this->makeReservation($user, 'OVL-XSS01', 'Garden Suite');

        $html = (new ReservationConfirmedNotification($reservation))->toMail($user)->render();

        $this->assertStringContainsString('&lt;script&gt;', $html);
        $this->assertStringNotContainsString("<script>alert('xss')</script>", $html);
    }

    public function test_cancellation_mail_is_rendered_in_english(): void
    {
        app()->setLocale('en');

        $user = User::factory()->make(['name' => 'Ana Guest']);
        $reservation = $this->makeReservation($user, 'OVL-EN01', 'Ocean Suite');

        $html = (new ReservationCancelledNotification($reservation))->toMail($user)->render();

        $this->assertStringContainsString('Your reservation was cancelled', $html);
        $this->assertStringContainsString('OVL-EN01', $html);
        $this->assertStringContainsString('View reservation', $html);
    }

    public function test_room_delivery_reminder_mail_includes_hours_and_time(): void
    {
        $user = User::factory()->make(['name' => 'Ana Huésped']);
        $reservation = $this->makeReservation($user, 'OVL-DEL01', 'Garden Suite');

        $html = (new RoomDeliveryReminderNotification($reservation, 4))->toMail($user)->render();

        $this->assertStringContainsString('4 hora', $html);
        $this->assertStringContainsString('15:00', $html);
        $this->assertStringContainsString('OVL-DEL01', $html);
        $this->assertStringContainsString('Overlook Resort &amp; Spa', $html);
    }

    public function test_verification_mail_includes_signed_link_and_branding(): void
    {
        $user = User::factory()->make([
            'id' => 42,
            'name' => 'Ana Huésped',
            'email' => 'ana@overlook.test',
        ]);

        $html = (new VerifyEmailNotification)->toMail($user)->render();

        $this->assertStringContainsString('Overlook Resort &amp; Spa', $html);
        $this->assertStringContainsString('Ana Huésped', $html);
        $this->assertStringContainsString('/email/verify/42/', $html);
        $this->assertStringContainsString('Confirma tu correo', $html);
    }

    private function makeReservation(User $user, string $code, string $roomName): Reservation
    {
        $roomType = new RoomType(['name' => $roomName]);
        $room = new Room(['number' => '101']);
        $room->setRelation('roomType', $roomType);

        $reservation = new Reservation([
            'code' => $code,
            'check_in_date' => '2026-10-01',
            'check_out_date' => '2026-10-04',
        ]);
        $reservation->id = 17;
        $reservation->setRelation('user', $user);
        $reservation->setRelation('room', $room);

        return $reservation;
    }
}

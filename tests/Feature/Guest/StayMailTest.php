<?php

namespace Tests\Feature\Guest;

use App\Enums\NotificationType;
use App\Enums\UserRole;
use App\Models\Reservation;
use App\Models\Room;
use App\Models\RoomType;
use App\Models\User;
use App\Notifications\ReservationCancelledNotification;
use App\Notifications\ReservationConfirmedNotification;
use App\Notifications\RoomDeliveryReminderNotification;
use App\Services\NotificationService;
use App\Services\ReservationService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class StayMailTest extends TestCase
{
    use RefreshDatabase;

    public function test_creating_a_reservation_sends_confirmation_mail_once(): void
    {
        Notification::fake();

        $user = User::factory()->create(['role' => UserRole::Guest]);
        $room = Room::factory()->for(RoomType::factory()->create(['max_guests' => 4]))->create();
        $checkIn = Carbon::today()->addDays(4);
        $checkOut = Carbon::today()->addDays(6);
        $service = app(ReservationService::class);
        $key = $service->buildIdempotencyKey($user, $room, $checkIn, $checkOut, 2);

        $service->create($user, $room, $checkIn, $checkOut, 2, $key);
        $service->create($user, $room, $checkIn, $checkOut, 2, $key);

        Notification::assertSentToTimes($user, ReservationConfirmedNotification::class, 1);
        $this->assertSame(1, Reservation::query()->count());
        $this->assertDatabaseHas('user_notifications', [
            'user_id' => $user->id,
            'type' => NotificationType::ReservationConfirmed->value,
        ]);
    }

    public function test_cancelling_a_reservation_sends_cancellation_mail(): void
    {
        Notification::fake();

        $user = User::factory()->create(['role' => UserRole::Guest]);
        $room = Room::factory()->for(RoomType::factory()->create(['max_guests' => 4]))->create();
        $checkIn = Carbon::today()->addDays(4);
        $checkOut = Carbon::today()->addDays(6);
        $service = app(ReservationService::class);
        $key = $service->buildIdempotencyKey($user, $room, $checkIn, $checkOut, 2);
        $reservation = $service->create($user, $room, $checkIn, $checkOut, 2, $key);

        $service->cancel($user, $reservation);

        Notification::assertSentTo($user, ReservationCancelledNotification::class);
        $this->assertDatabaseHas('user_notifications', [
            'user_id' => $user->id,
            'reservation_id' => $reservation->id,
            'type' => NotificationType::ReservationCancelled->value,
        ]);
    }

    public function test_room_delivery_reminder_sends_mail_once_per_interval(): void
    {
        Notification::fake();

        $user = User::factory()->create(['role' => UserRole::Guest]);
        $reservation = Reservation::factory()->for($user)->create();
        $service = app(NotificationService::class);

        $service->sendRoomDeliveryReminder($reservation, 4);
        $service->sendRoomDeliveryReminder($reservation, 4);

        Notification::assertSentToTimes($user, RoomDeliveryReminderNotification::class, 1);
    }
}

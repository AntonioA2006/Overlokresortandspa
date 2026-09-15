<?php

namespace Tests\Feature\Guest;

use App\Enums\ConversationStatus;
use App\Enums\NotificationType;
use App\Enums\UserRole;
use App\Models\Conversation;
use App\Models\Room;
use App\Models\RoomType;
use App\Models\User;
use App\Models\UserNotification;
use App\Services\ReservationService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NotificationsTest extends TestCase
{
    use RefreshDatabase;

    public function test_creating_a_reservation_creates_a_confirmation_notification(): void
    {
        $user = User::factory()->create(['role' => UserRole::Guest]);
        $room = Room::factory()->for(RoomType::factory()->create(['max_guests' => 4]))->create();
        $checkIn = Carbon::today()->addDays(4);
        $checkOut = Carbon::today()->addDays(6);
        $service = app(ReservationService::class);
        $key = $service->buildIdempotencyKey($user, $room, $checkIn, $checkOut, 2);

        $reservation = $service->create($user, $room, $checkIn, $checkOut, 2, $key);

        $this->assertDatabaseHas('user_notifications', [
            'user_id' => $user->id,
            'reservation_id' => $reservation->id,
            'type' => NotificationType::ReservationConfirmed->value,
        ]);
    }

    public function test_guest_can_view_and_mark_notifications_as_read(): void
    {
        $user = User::factory()->create(['role' => UserRole::Guest]);
        $notification = UserNotification::query()->create([
            'user_id' => $user->id,
            'type' => NotificationType::HotelInfo,
            'title' => 'Bienvenida',
            'message' => 'Tu estancia comienza pronto.',
            'dedupe_key' => 'hotel_info:welcome:'.$user->id,
            'sent_at' => now(),
        ]);

        $this->actingAs($user)
            ->get(route('guest.notifications.index'))
            ->assertSee('Bienvenida')
            ->assertSee('Tu estancia comienza pronto.');

        $this->actingAs($user)
            ->post(route('guest.notifications.read', $notification))
            ->assertRedirect();

        $this->assertNotNull($notification->fresh()->read_at);
    }

    public function test_confirmation_notification_links_to_the_reservation(): void
    {
        $user = User::factory()->create(['role' => UserRole::Guest]);
        $room = Room::factory()->for(RoomType::factory()->create(['max_guests' => 4]))->create();
        $checkIn = Carbon::today()->addDays(4);
        $checkOut = Carbon::today()->addDays(6);
        $service = app(ReservationService::class);
        $key = $service->buildIdempotencyKey($user, $room, $checkIn, $checkOut, 2);
        $reservation = $service->create($user, $room, $checkIn, $checkOut, 2, $key);

        $this->actingAs($user)
            ->get(route('guest.notifications.index'))
            ->assertSee('Ver reservación')
            ->assertSee(route('guest.reservations.show', $reservation), false);
    }

    public function test_support_notification_links_staff_to_the_conversation(): void
    {
        $guest = User::factory()->create(['role' => UserRole::Guest]);
        $agent = User::factory()->create(['role' => UserRole::Support]);
        $conversation = Conversation::query()->create([
            'user_id' => $guest->id,
            'status' => ConversationStatus::Waiting,
        ]);
        UserNotification::query()->create([
            'user_id' => $agent->id,
            'type' => NotificationType::SupportMessage,
            'title' => 'Nuevo mensaje de soporte',
            'message' => '¿A qué hora es el spa?',
            'dedupe_key' => 'support_message:staff-link:'.$conversation->id,
            'data' => ['conversation_id' => $conversation->id],
            'sent_at' => now(),
        ]);

        $this->actingAs($agent)
            ->get(route('guest.notifications.index'))
            ->assertSee('Abrir chat')
            ->assertSee(route('support.conversations.show', $conversation), false);
    }

    public function test_guest_cannot_mark_another_users_notification(): void
    {
        $owner = User::factory()->create(['role' => UserRole::Guest]);
        $intruder = User::factory()->create(['role' => UserRole::Guest]);
        $notification = UserNotification::query()->create([
            'user_id' => $owner->id,
            'type' => NotificationType::HotelInfo,
            'title' => 'Privada',
            'message' => 'No deberías ver esto.',
            'dedupe_key' => 'hotel_info:private:'.$owner->id,
            'sent_at' => now(),
        ]);

        $this->actingAs($intruder)
            ->post(route('guest.notifications.read', $notification))
            ->assertForbidden();
    }
}

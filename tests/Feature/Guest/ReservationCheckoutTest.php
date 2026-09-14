<?php

namespace Tests\Feature\Guest;

use App\Enums\AuditAction;
use App\Enums\ReservationStatus;
use App\Enums\UserRole;
use App\Models\Reservation;
use App\Models\Room;
use App\Models\RoomType;
use App\Models\User;
use App\Services\ReservationService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReservationCheckoutTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @return array<string, mixed>
     */
    private function searchParams(array $overrides = []): array
    {
        return array_merge([
            'check_in_date' => Carbon::today()->addDays(3)->toDateString(),
            'check_out_date' => Carbon::today()->addDays(6)->toDateString(),
            'adults' => 2,
            'children' => 0,
        ], $overrides);
    }

    private function guestUser(): User
    {
        return User::factory()->create(['role' => UserRole::Guest]);
    }

    public function test_checkout_requires_authentication(): void
    {
        $room = Room::factory()->create();

        $this->get(route('guest.reservations.checkout', array_merge(['room' => $room->id], $this->searchParams())))
            ->assertRedirect(route('login'));
    }

    public function test_checkout_renders_with_backend_pricing(): void
    {
        $roomType = RoomType::factory()->create([
            'name' => 'Suite Ocean View',
            'base_price_per_night' => 2450,
            'max_guests' => 4,
        ]);
        $room = Room::factory()->for($roomType)->create();
        $user = $this->guestUser();

        $response = $this->actingAs($user)->get(route('guest.reservations.checkout', array_merge(
            ['room' => $room->id],
            $this->searchParams(),
        )));

        $response->assertOk();
        $response->assertSee('Confirma tu estancia');
        $response->assertSee('Suite Ocean View');
        $response->assertSee('2,450');
        $response->assertSee('7,350');
        $response->assertSee('Confirmar reservación');
    }

    public function test_checkout_redirects_when_room_is_no_longer_available(): void
    {
        $roomType = RoomType::factory()->create(['slug' => 'reserved-suite', 'max_guests' => 4]);
        $room = Room::factory()->for($roomType)->create();

        Reservation::factory()->for($room)->create([
            'check_in_date' => Carbon::today()->addDays(4)->toDateString(),
            'check_out_date' => Carbon::today()->addDays(7)->toDateString(),
        ]);

        $params = $this->searchParams();
        $user = $this->guestUser();

        $response = $this->actingAs($user)->get(route('guest.reservations.checkout', array_merge(
            ['room' => $room->id],
            $params,
        )));

        $response->assertRedirect(route('guest.reservations.rooms.show', array_merge(
            ['roomType' => $roomType->slug],
            $params,
        )));
        $response->assertSessionHas('reservation_error');
    }

    public function test_store_creates_pending_reservation_with_backend_pricing(): void
    {
        $roomType = RoomType::factory()->create([
            'base_price_per_night' => 1800,
            'max_guests' => 4,
        ]);
        $room = Room::factory()->for($roomType)->create();
        $user = $this->guestUser();
        $params = $this->searchParams();

        $idempotencyKey = app(ReservationService::class)->buildIdempotencyKey(
            $user,
            $room,
            Carbon::parse($params['check_in_date']),
            Carbon::parse($params['check_out_date']),
            2,
        );

        $response = $this->actingAs($user)->post(route('guest.reservations.store'), array_merge($params, [
            'room_id' => $room->id,
            'idempotency_key' => $idempotencyKey,
        ]));

        $response->assertRedirect(route('guest.reservations.show', Reservation::query()->first()));

        $reservation = Reservation::query()->first();

        $this->assertNotNull($reservation);
        $this->assertSame($user->id, $reservation->user_id);
        $this->assertSame($room->id, $reservation->room_id);
        $this->assertSame(ReservationStatus::Confirmed, $reservation->status);
        $this->assertNotNull($reservation->check_in_token);
        $this->assertNull($reservation->token_revoked_at);
        $this->assertNotNull($reservation->confirmed_at);
        $this->assertSame('5400.00', (string) $reservation->price_total);
        $this->assertSame(2, $reservation->guests_count);
        $this->assertSame($idempotencyKey, $reservation->idempotency_key);
        $this->assertStringStartsWith('OVL-', $reservation->code);

        $this->assertDatabaseHas('audit_logs', [
            'action' => AuditAction::ReservationCreated->value,
            'auditable_id' => $reservation->id,
        ]);

        $this->assertDatabaseHas('audit_logs', [
            'action' => AuditAction::ReservationConfirmed->value,
            'auditable_id' => $reservation->id,
        ]);
    }

    public function test_store_is_idempotent_for_duplicate_submissions(): void
    {
        $room = Room::factory()->create(['room_type_id' => RoomType::factory()->create(['max_guests' => 4])->id]);
        $user = $this->guestUser();
        $params = $this->searchParams();

        $idempotencyKey = app(ReservationService::class)->buildIdempotencyKey(
            $user,
            $room,
            Carbon::parse($params['check_in_date']),
            Carbon::parse($params['check_out_date']),
            2,
        );

        $payload = array_merge($params, [
            'room_id' => $room->id,
            'idempotency_key' => $idempotencyKey,
        ]);

        $this->actingAs($user)->post(route('guest.reservations.store'), $payload);
        $this->actingAs($user)->post(route('guest.reservations.store'), $payload);

        $this->assertSame(1, Reservation::query()->count());
    }

    public function test_store_prevents_overbooking_after_existing_reservation(): void
    {
        $room = Room::factory()->create(['room_type_id' => RoomType::factory()->create(['max_guests' => 4])->id]);
        $user = $this->guestUser();
        $params = $this->searchParams();

        Reservation::factory()->for($room)->create([
            'check_in_date' => $params['check_in_date'],
            'check_out_date' => $params['check_out_date'],
            'status' => ReservationStatus::Confirmed,
        ]);

        $idempotencyKey = app(ReservationService::class)->buildIdempotencyKey(
            $user,
            $room,
            Carbon::parse($params['check_in_date']),
            Carbon::parse($params['check_out_date']),
            2,
        );

        $response = $this->actingAs($user)->post(route('guest.reservations.store'), array_merge($params, [
            'room_id' => $room->id,
            'idempotency_key' => $idempotencyKey,
        ]));

        $response->assertRedirect();
        $response->assertSessionHas('reservation_error');
        $this->assertSame(1, Reservation::query()->count());
    }

    public function test_guest_can_view_own_reservation_but_not_others(): void
    {
        $owner = $this->guestUser();
        $other = $this->guestUser();
        $reservation = Reservation::factory()->for($owner)->create();

        $this->actingAs($owner)
            ->get(route('guest.reservations.show', $reservation))
            ->assertOk()
            ->assertSee($reservation->code);

        $this->actingAs($other)
            ->get(route('guest.reservations.show', $reservation))
            ->assertForbidden();
    }

    public function test_store_rejects_tampered_idempotency_key(): void
    {
        $room = Room::factory()->create(['room_type_id' => RoomType::factory()->create(['max_guests' => 4])->id]);
        $user = $this->guestUser();

        $response = $this->actingAs($user)->post(route('guest.reservations.store'), array_merge(
            $this->searchParams(),
            [
                'room_id' => $room->id,
                'idempotency_key' => 'tampered-key',
            ],
        ));

        $response->assertSessionHasErrors('idempotency_key');
        $this->assertSame(0, Reservation::query()->count());
    }

    public function test_checkout_is_translated_in_english(): void
    {
        $roomType = RoomType::factory()->create(['max_guests' => 4]);
        $room = Room::factory()->for($roomType)->create();
        $user = $this->guestUser();

        $response = $this->withSession(['locale' => 'en'])
            ->actingAs($user)
            ->get(route('guest.reservations.checkout', array_merge(
                ['room' => $room->id],
                $this->searchParams(),
            )));

        $response->assertOk();
        $response->assertSee('Confirm your stay');
        $response->assertSee('Confirm reservation');
    }
}

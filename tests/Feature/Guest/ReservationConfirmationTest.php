<?php

namespace Tests\Feature\Guest;

use App\Enums\ReservationStatus;
use App\Enums\UserRole;
use App\Models\Reservation;
use App\Models\Room;
use App\Models\RoomType;
use App\Models\User;
use App\Services\ReservationService;
use App\Services\ReservationTokenService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReservationConfirmationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @return array<string, mixed>
     */
    private function searchParams(): array
    {
        return [
            'check_in_date' => Carbon::today()->addDays(3)->toDateString(),
            'check_out_date' => Carbon::today()->addDays(6)->toDateString(),
            'adults' => 2,
            'children' => 0,
        ];
    }

    public function test_confirmation_page_shows_qr_check_in_url_without_exposing_sensitive_data(): void
    {
        $user = User::factory()->create(['role' => UserRole::Guest]);
        $roomType = RoomType::factory()->create(['name' => 'Garden Suite']);
        $room = Room::factory()->for($roomType)->create();

        $reservation = Reservation::factory()->for($user)->for($room)->create([
            'status' => ReservationStatus::Confirmed,
            'check_in_token' => null,
        ]);

        $response = $this->actingAs($user)->get(route('guest.reservations.show', $reservation));

        $response->assertOk();
        $response->assertSee('Reservación confirmada');
        $response->assertSee('Tu código QR');
        $response->assertSee('data-reservation-qr', false);
        $response->assertSee('reception/check/', false);
        $response->assertSee($reservation->code);
        $response->assertDontSee($user->email);

        $reservation->refresh();
        $this->assertNotNull($reservation->check_in_token);
    }

    public function test_store_redirect_shows_confirmed_message(): void
    {
        $user = User::factory()->create(['role' => UserRole::Guest]);
        $room = Room::factory()->create(['room_type_id' => RoomType::factory()->create(['max_guests' => 4])->id]);
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
        $response->assertSessionHas('status', __('reservations.created_confirmed'));
    }

    public function test_qr_url_points_to_reception_check_route(): void
    {
        $reservation = Reservation::factory()->create([
            'status' => ReservationStatus::Confirmed,
            'check_in_token' => 'secure-token-abc',
        ]);

        $url = app(ReservationTokenService::class)->buildCheckUrl('secure-token-abc');

        $this->assertStringContainsString('/reception/check/secure-token-abc', $url);
        $this->assertStringNotContainsString($reservation->user->email, $url);
    }

    public function test_confirmation_page_is_translated_in_english(): void
    {
        $user = User::factory()->create(['role' => UserRole::Guest]);
        $reservation = Reservation::factory()->for($user)->create([
            'status' => ReservationStatus::Confirmed,
            'check_in_token' => 'token-en-test',
        ]);

        $response = $this->withSession(['locale' => 'en'])
            ->actingAs($user)
            ->get(route('guest.reservations.show', $reservation));

        $response->assertOk();
        $response->assertSee('Reservation confirmed');
        $response->assertSee('Your QR code');
        $response->assertSee('Confirmed');
    }
}

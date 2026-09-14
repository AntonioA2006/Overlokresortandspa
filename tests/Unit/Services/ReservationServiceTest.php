<?php

namespace Tests\Unit\Services;

use App\Enums\ReservationStatus;
use App\Enums\UserRole;
use App\Exceptions\ReservationUnavailableException;
use App\Models\Reservation;
use App\Models\Room;
use App\Models\RoomType;
use App\Models\User;
use App\Services\ReservationService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReservationServiceTest extends TestCase
{
    use RefreshDatabase;

    private ReservationService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = app(ReservationService::class);
    }

    public function test_it_creates_a_confirmed_reservation_with_token(): void
    {
        $user = User::factory()->create(['role' => UserRole::Guest]);
        $roomType = RoomType::factory()->create(['base_price_per_night' => 2000, 'max_guests' => 4]);
        $room = Room::factory()->for($roomType)->create();
        $checkIn = Carbon::today()->addDays(5);
        $checkOut = Carbon::today()->addDays(8);

        $key = $this->service->buildIdempotencyKey($user, $room, $checkIn, $checkOut, 2);

        $reservation = $this->service->create($user, $room, $checkIn, $checkOut, 2, $key);

        $this->assertSame(ReservationStatus::Confirmed, $reservation->status);
        $this->assertSame('6000.00', $reservation->price_total);
        $this->assertNotNull($reservation->check_in_token);
        $this->assertTrue($reservation->isTokenActive());
    }

    public function test_it_throws_when_room_is_unavailable(): void
    {
        $user = User::factory()->create(['role' => UserRole::Guest]);
        $room = Room::factory()->create(['room_type_id' => RoomType::factory()->create(['max_guests' => 4])->id]);

        Reservation::factory()->for($room)->create([
            'check_in_date' => Carbon::today()->addDays(4)->toDateString(),
            'check_out_date' => Carbon::today()->addDays(7)->toDateString(),
            'status' => ReservationStatus::Confirmed,
        ]);

        $checkIn = Carbon::today()->addDays(3);
        $checkOut = Carbon::today()->addDays(6);
        $key = $this->service->buildIdempotencyKey($user, $room, $checkIn, $checkOut, 2);

        $this->expectException(ReservationUnavailableException::class);

        $this->service->create($user, $room, $checkIn, $checkOut, 2, $key);
    }
}

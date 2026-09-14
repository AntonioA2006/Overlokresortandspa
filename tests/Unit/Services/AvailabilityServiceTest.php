<?php

namespace Tests\Unit\Services;

use App\Enums\ReservationStatus;
use App\Enums\RoomStatus;
use App\Models\Reservation;
use App\Models\Room;
use App\Models\RoomType;
use App\Services\AvailabilityService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AvailabilityServiceTest extends TestCase
{
    use RefreshDatabase;

    private AvailabilityService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = app(AvailabilityService::class);
    }

    public function test_it_returns_available_room_for_valid_search(): void
    {
        $roomType = RoomType::factory()->create(['max_guests' => 2]);
        $room = Room::factory()->for($roomType)->create(['status' => RoomStatus::Available]);

        $checkIn = Carbon::today()->addDays(3);
        $checkOut = Carbon::today()->addDays(6);

        $results = $this->service->availableRooms($checkIn, $checkOut, 2);

        $this->assertTrue($results->contains('id', $room->id));
    }

    public function test_it_excludes_reserved_room_with_overlapping_dates(): void
    {
        $room = Room::factory()->create(['status' => RoomStatus::Available]);

        Reservation::factory()->for($room)->create([
            'check_in_date' => Carbon::today()->addDays(4)->toDateString(),
            'check_out_date' => Carbon::today()->addDays(7)->toDateString(),
            'status' => ReservationStatus::Confirmed,
        ]);

        $results = $this->service->availableRooms(
            Carbon::today()->addDays(3),
            Carbon::today()->addDays(6),
            2,
        );

        $this->assertFalse($results->contains('id', $room->id));
    }

    public function test_it_excludes_room_with_unavailable_status(): void
    {
        $room = Room::factory()->maintenance()->create();

        $results = $this->service->availableRooms(
            Carbon::today()->addDays(3),
            Carbon::today()->addDays(6),
            2,
        );

        $this->assertFalse($results->contains('id', $room->id));
    }

    public function test_it_excludes_room_types_with_insufficient_capacity(): void
    {
        $roomType = RoomType::factory()->create(['max_guests' => 2]);
        $room = Room::factory()->for($roomType)->create(['status' => RoomStatus::Available]);

        $results = $this->service->availableRooms(
            Carbon::today()->addDays(3),
            Carbon::today()->addDays(6),
            3,
        );

        $this->assertFalse($results->contains('id', $room->id));
    }

    public function test_it_groups_available_rooms_by_room_type(): void
    {
        $roomType = RoomType::factory()->create(['name' => 'Suite Ocean View']);
        Room::factory()->count(2)->for($roomType)->create(['status' => RoomStatus::Available]);

        $groups = $this->service->availableRoomsGroupedByType(
            Carbon::today()->addDays(3),
            Carbon::today()->addDays(6),
            2,
        );

        $this->assertCount(1, $groups);
        $this->assertSame('Suite Ocean View', $groups->first()['room_type']->name);
        $this->assertSame(2, $groups->first()['available_count']);
    }

    public function test_is_room_available_returns_false_for_blocked_room(): void
    {
        $room = Room::factory()->maintenance()->create();

        $this->assertFalse($this->service->isRoomAvailable(
            $room,
            Carbon::today()->addDays(3),
            Carbon::today()->addDays(6),
            2,
        ));
    }

    public function test_it_returns_available_rooms_for_specific_room_type(): void
    {
        $roomType = RoomType::factory()->create(['max_guests' => 4]);
        $otherType = RoomType::factory()->create(['max_guests' => 4]);
        $room = Room::factory()->for($roomType)->create(['status' => RoomStatus::Available]);
        Room::factory()->for($otherType)->create(['status' => RoomStatus::Available]);

        $results = $this->service->availableRoomsForType(
            $roomType,
            Carbon::today()->addDays(3),
            Carbon::today()->addDays(6),
            2,
        );

        $this->assertCount(1, $results);
        $this->assertTrue($results->contains('id', $room->id));
    }

    public function test_available_rooms_for_type_returns_empty_when_capacity_exceeded(): void
    {
        $roomType = RoomType::factory()->create(['max_guests' => 2]);
        Room::factory()->for($roomType)->create(['status' => RoomStatus::Available]);

        $results = $this->service->availableRoomsForType(
            $roomType,
            Carbon::today()->addDays(3),
            Carbon::today()->addDays(6),
            3,
        );

        $this->assertTrue($results->isEmpty());
    }
}

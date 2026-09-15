<?php

namespace Tests\Feature\Guest;

use App\Models\Amenity;
use App\Models\Reservation;
use App\Models\Room;
use App\Models\RoomPhoto;
use App\Models\RoomType;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReservationRoomShowTest extends TestCase
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

    public function test_room_detail_renders_with_search_context_pricing_and_book_cta(): void
    {
        $roomType = RoomType::factory()->create([
            'name' => 'Suite Ocean View',
            'slug' => 'suite-ocean-view',
            'description' => 'Suite amplia con vista al mar.',
            'base_price_per_night' => 2450,
            'max_guests' => 4,
        ]);
        $room = Room::factory()->for($roomType)->create();
        $amenity = Amenity::query()->create(['name' => 'Wi-Fi']);
        $roomType->amenities()->attach($amenity);
        RoomPhoto::factory()->for($room)->create([
            'path' => 'images/landing/room.jpg',
            'alt_text' => 'Suite Ocean View — Overlook Resort & Spa',
        ]);

        $params = $this->searchParams();

        $response = $this->get(route('guest.reservations.rooms.show', array_merge(
            ['roomType' => $roomType->slug],
            $params,
        )));

        $response->assertOk();
        $response->assertSee('Suite Ocean View');
        $response->assertSee('Suite amplia con vista al mar.');
        $response->assertSee('Wi-Fi');
        $response->assertSee('2,450');
        $response->assertSee('7,350');
        $response->assertSee('Reservar');
        $response->assertSee('images/landing/room.jpg', false);
        $response->assertSee('guest/reservations/checkout/'.$room->id, false);
        $response->assertSee('check_in_date='.$params['check_in_date'], false);
        $response->assertDontSee('Detalle próximamente');
    }

    public function test_room_detail_shows_unavailable_state_when_room_is_reserved(): void
    {
        $roomType = RoomType::factory()->create([
            'name' => 'Reserved Suite',
            'slug' => 'reserved-suite',
            'max_guests' => 4,
        ]);
        $room = Room::factory()->for($roomType)->create();

        Reservation::factory()->for($room)->create([
            'check_in_date' => Carbon::today()->addDays(4)->toDateString(),
            'check_out_date' => Carbon::today()->addDays(7)->toDateString(),
        ]);

        $response = $this->get(route('guest.reservations.rooms.show', array_merge(
            ['roomType' => $roomType->slug],
            $this->searchParams(),
        )));

        $response->assertOk();
        $response->assertSee('No disponible para estas fechas');
        $response->assertSee('Cambiar fechas');
        $response->assertDontSee('room-detail__cta', false);
    }

    public function test_room_detail_shows_capacity_message_when_guest_count_exceeds_limit(): void
    {
        $roomType = RoomType::factory()->create([
            'name' => 'Compact Room',
            'slug' => 'compact-room',
            'max_guests' => 2,
        ]);
        Room::factory()->for($roomType)->create();

        $response = $this->get(route('guest.reservations.rooms.show', array_merge(
            ['roomType' => $roomType->slug],
            $this->searchParams([
                'adults' => 3,
                'children' => 0,
            ]),
        )));

        $response->assertOk();
        $response->assertSee('admite hasta 2 huéspedes');
        $response->assertDontSee('room-detail__cta', false);
    }

    public function test_room_detail_without_search_context_prompts_for_dates(): void
    {
        $roomType = RoomType::factory()->create([
            'name' => 'Browse Suite',
            'slug' => 'browse-suite',
        ]);
        Room::factory()->for($roomType)->create();

        $response = $this->get(route('guest.reservations.rooms.show', $roomType));

        $response->assertOk();
        $response->assertSee('Browse Suite');
        $response->assertSee('Selecciona tus fechas');
        $response->assertSee('Consultar fechas');
        $response->assertDontSee('room-detail__cta', false);
    }

    public function test_inactive_room_type_returns_not_found(): void
    {
        $roomType = RoomType::factory()->create([
            'slug' => 'inactive-suite',
            'is_active' => false,
        ]);

        $this->get(route('guest.reservations.rooms.show', array_merge(
            ['roomType' => $roomType->slug],
            $this->searchParams(),
        )))->assertNotFound();
    }

    public function test_pricing_on_room_detail_comes_from_backend_not_user_input(): void
    {
        $roomType = RoomType::factory()->create([
            'slug' => 'pricing-suite',
            'base_price_per_night' => 1800,
            'max_guests' => 4,
        ]);
        Room::factory()->for($roomType)->create();

        $response = $this->get(route('guest.reservations.rooms.show', array_merge(
            ['roomType' => $roomType->slug],
            $this->searchParams(),
            ['price' => 99],
        )));

        $response->assertOk();
        $response->assertSee('1,800');
        $response->assertSee('5,400');
        $response->assertDontSee('$99');
    }

    public function test_room_detail_is_translated_in_english(): void
    {
        $roomType = RoomType::factory()->create([
            'name' => 'Garden Suite',
            'slug' => 'garden-suite',
            'base_price_per_night' => 1500,
            'max_guests' => 4,
        ]);
        $room = Room::factory()->for($roomType)->create();
        $amenity = Amenity::query()->create(['name' => 'Ocean view']);
        $roomType->amenities()->attach($amenity);

        $response = $this->withSession(['locale' => 'en'])
            ->get(route('guest.reservations.rooms.show', array_merge(
                ['roomType' => $roomType->slug],
                $this->searchParams(),
            )));

        $response->assertOk();
        $response->assertSee('Your stay');
        $response->assertSee('Amenities');
        $response->assertSee('Estimated total');
        $response->assertSee('Reserve');
        $response->assertSee('Back to results');
    }

    public function test_invalid_search_params_redirect_to_search_with_errors(): void
    {
        $roomType = RoomType::factory()->create(['slug' => 'validation-suite']);
        Room::factory()->for($roomType)->create();

        $response = $this->from(route('guest.reservations.search'))
            ->get(route('guest.reservations.rooms.show', [
                'roomType' => $roomType->slug,
                'check_in_date' => Carbon::today()->subDay()->toDateString(),
                'check_out_date' => Carbon::today()->addDays(2)->toDateString(),
                'adults' => 2,
            ]));

        $response->assertRedirect(route('guest.reservations.search'));
        $response->assertSessionHasErrors('check_in_date');
    }
}

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

class ReservationResultsTest extends TestCase
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

    public function test_results_page_renders_with_available_room_types(): void
    {
        $roomType = RoomType::factory()->create([
            'name' => 'Suite Ocean View',
            'slug' => 'suite-ocean-view',
            'base_price_per_night' => 3200,
            'max_guests' => 4,
        ]);
        Room::factory()->for($roomType)->create();

        $response = $this->get(route('guest.reservations.results', $this->searchParams()));

        $response->assertOk();
        $response->assertSee('Tu estancia');
        $response->assertSee('Habitaciones disponibles');
        $response->assertSee('Suite Ocean View');
        $response->assertSee('3,200');
        $response->assertSee('Ver habitación');
        $response->assertSee('noindex, nofollow', false);
    }

    public function test_results_page_displays_search_summary(): void
    {
        $roomType = RoomType::factory()->create();
        Room::factory()->for($roomType)->create();

        $response = $this->get(route('guest.reservations.results', $this->searchParams([
            'adults' => 2,
            'children' => 1,
        ])));

        $response->assertOk();
        $response->assertSee('2 adultos');
        $response->assertSee('1 niño');
        $response->assertSee('Modificar búsqueda');
    }

    public function test_results_page_shows_amenities_and_room_photo(): void
    {
        $roomType = RoomType::factory()->create(['name' => 'Deluxe Garden']);
        $room = Room::factory()->for($roomType)->create();
        $amenity = Amenity::query()->create(['name' => 'Wi-Fi']);
        $roomType->amenities()->attach($amenity);
        RoomPhoto::factory()->for($room)->create([
            'path' => 'images/landing/room.jpg',
            'alt_text' => 'Deluxe Garden — Overlook Resort & Spa',
        ]);

        $response = $this->get(route('guest.reservations.results', $this->searchParams()));

        $response->assertOk();
        $response->assertSee('Wi-Fi');
        $response->assertSee('images/landing/room.jpg', false);
        $response->assertSee('Deluxe Garden — Overlook Resort & Spa');
    }

    public function test_view_room_cta_links_to_room_type_show_route_with_search_params(): void
    {
        $roomType = RoomType::factory()->create([
            'name' => 'Standard Comfort',
            'slug' => 'standard-comfort',
            'max_guests' => 4,
        ]);
        Room::factory()->for($roomType)->create();

        $params = $this->searchParams(['children' => 1]);

        $response = $this->get(route('guest.reservations.results', $params));

        $response->assertOk();
        $response->assertSee('guest/reservations/rooms/standard-comfort', false);
        $response->assertSee('check_in_date=2026-09-17', false);
        $response->assertSee('children=1', false);
    }

    public function test_results_exclude_room_types_with_insufficient_capacity(): void
    {
        $smallType = RoomType::factory()->create(['name' => 'Tiny Room', 'max_guests' => 2]);
        $largeType = RoomType::factory()->create(['name' => 'Family Suite', 'max_guests' => 4]);

        Room::factory()->for($smallType)->create();
        Room::factory()->for($largeType)->create();

        $response = $this->get(route('guest.reservations.results', $this->searchParams([
            'adults' => 3,
            'children' => 1,
        ])));

        $response->assertOk();
        $response->assertSee('Family Suite');
        $response->assertDontSee('Tiny Room');
    }

    public function test_results_exclude_reserved_rooms(): void
    {
        $roomType = RoomType::factory()->create(['name' => 'Reserved Suite']);
        $room = Room::factory()->for($roomType)->create();

        Reservation::factory()->for($room)->create([
            'check_in_date' => Carbon::today()->addDays(4)->toDateString(),
            'check_out_date' => Carbon::today()->addDays(7)->toDateString(),
        ]);

        $response = $this->get(route('guest.reservations.results', $this->searchParams()));

        $response->assertOk();
        $response->assertDontSee('Reserved Suite');
    }

    public function test_results_exclude_physically_unavailable_rooms(): void
    {
        $availableType = RoomType::factory()->create(['name' => 'Open Suite']);
        $blockedType = RoomType::factory()->create(['name' => 'Closed Suite']);

        Room::factory()->for($availableType)->create();
        Room::factory()->for($blockedType)->maintenance()->create();

        $response = $this->get(route('guest.reservations.results', $this->searchParams()));

        $response->assertOk();
        $response->assertSee('Open Suite');
        $response->assertDontSee('Closed Suite');
    }

    public function test_empty_state_is_premium_and_allows_modify_search(): void
    {
        $response = $this->get(route('guest.reservations.results', $this->searchParams([
            'check_in_date' => Carbon::today()->addDays(30)->toDateString(),
            'check_out_date' => Carbon::today()->addDays(33)->toDateString(),
        ])));

        $response->assertOk();
        $response->assertSee('No encontramos habitaciones disponibles');
        $response->assertSee('No encontramos habitaciones disponibles para estas fechas.');
        $response->assertSee('Cambiar fechas');
    }

    public function test_results_page_is_translated_in_english(): void
    {
        $roomType = RoomType::factory()->create([
            'name' => 'Suite Ocean View',
            'max_guests' => 4,
        ]);
        Room::factory()->for($roomType)->create();

        $response = $this->withSession(['locale' => 'en'])
            ->get(route('guest.reservations.results', $this->searchParams([
                'children' => 1,
            ])));

        $response->assertOk();
        $response->assertSee('Your stay');
        $response->assertSee('Available rooms');
        $response->assertSee('View room');
        $response->assertSee('2 adults');
        $response->assertSee('1 child');
    }

    public function test_pricing_comes_from_backend_not_user_input(): void
    {
        $roomType = RoomType::factory()->create([
            'name' => 'Pricing Suite',
            'base_price_per_night' => 2450,
        ]);
        Room::factory()->for($roomType)->create();

        $response = $this->get(route('guest.reservations.results', array_merge(
            $this->searchParams(),
            ['price' => 99],
        )));

        $response->assertOk();
        $response->assertSee('2,450');
        $response->assertDontSee('$99');
    }

    public function test_room_show_route_is_accessible_for_active_room_type(): void
    {
        $roomType = RoomType::factory()->create(['slug' => 'suite-ocean-view']);

        $response = $this->get(route('guest.reservations.rooms.show', $roomType));

        $response->assertOk();
        $response->assertSee($roomType->name);
    }
}

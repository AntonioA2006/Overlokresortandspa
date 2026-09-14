<?php

namespace Tests\Feature\Guest;

use App\Models\Reservation;
use App\Models\Room;
use App\Models\RoomType;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReservationSearchTest extends TestCase
{
    use RefreshDatabase;

    public function test_search_page_is_accessible(): void
    {
        $response = $this->get(route('guest.reservations.search'));

        $response->assertOk();
        $response->assertSee('Encuentra tu estancia');
        $response->assertSee('Buscar disponibilidad');
    }

    public function test_results_page_shows_available_room_types(): void
    {
        $roomType = RoomType::factory()->create(['name' => 'Deluxe Garden']);
        Room::factory()->for($roomType)->create();

        $response = $this->get(route('guest.reservations.results', [
            'check_in_date' => Carbon::today()->addDays(3)->toDateString(),
            'check_out_date' => Carbon::today()->addDays(6)->toDateString(),
            'adults' => 2,
            'children' => 0,
        ]));

        $response->assertOk();
        $response->assertSee('Habitaciones disponibles');
        $response->assertSee('Deluxe Garden');
        $response->assertSee('Ver habitación');
    }

    public function test_results_page_hides_unavailable_rooms(): void
    {
        $availableType = RoomType::factory()->create(['name' => 'Suite Ocean View']);
        $blockedType = RoomType::factory()->create(['name' => 'Maintenance Suite']);

        Room::factory()->for($availableType)->create();
        Room::factory()->for($blockedType)->maintenance()->create();

        $response = $this->get(route('guest.reservations.results', [
            'check_in_date' => Carbon::today()->addDays(3)->toDateString(),
            'check_out_date' => Carbon::today()->addDays(6)->toDateString(),
            'adults' => 2,
            'children' => 0,
        ]));

        $response->assertOk();
        $response->assertSee('Suite Ocean View');
        $response->assertDontSee('Maintenance Suite');
    }

    public function test_results_page_shows_empty_state_when_no_rooms_are_available(): void
    {
        $room = Room::factory()->create();
        Reservation::factory()->for($room)->create([
            'check_in_date' => Carbon::today()->addDays(3)->toDateString(),
            'check_out_date' => Carbon::today()->addDays(6)->toDateString(),
        ]);

        $response = $this->get(route('guest.reservations.results', [
            'check_in_date' => Carbon::today()->addDays(4)->toDateString(),
            'check_out_date' => Carbon::today()->addDays(5)->toDateString(),
            'adults' => 2,
            'children' => 0,
        ]));

        $response->assertOk();
        $response->assertSee('No encontramos habitaciones disponibles');
        $response->assertSee('No encontramos habitaciones disponibles para estas fechas.');
        $response->assertSee('Cambiar fechas');
    }

    public function test_checkout_before_checkin_is_rejected(): void
    {
        $response = $this->from(route('guest.reservations.search'))
            ->get(route('guest.reservations.results', [
                'check_in_date' => Carbon::today()->addDays(5)->toDateString(),
                'check_out_date' => Carbon::today()->addDays(3)->toDateString(),
                'adults' => 2,
                'children' => 0,
            ]));

        $response->assertRedirect(route('guest.reservations.search'));
        $response->assertSessionHasErrors('check_out_date');
    }

    public function test_same_day_checkin_and_checkout_is_rejected(): void
    {
        $date = Carbon::today()->addDays(4)->toDateString();

        $response = $this->from(route('guest.reservations.search'))
            ->get(route('guest.reservations.results', [
                'check_in_date' => $date,
                'check_out_date' => $date,
                'adults' => 2,
                'children' => 0,
            ]));

        $response->assertRedirect(route('guest.reservations.search'));
        $response->assertSessionHasErrors('check_out_date');
    }

    public function test_past_checkin_date_is_rejected(): void
    {
        $response = $this->from(route('guest.reservations.search'))
            ->get(route('guest.reservations.results', [
                'check_in_date' => Carbon::today()->subDay()->toDateString(),
                'check_out_date' => Carbon::today()->addDays(2)->toDateString(),
                'adults' => 2,
                'children' => 0,
            ]));

        $response->assertRedirect(route('guest.reservations.search'));
        $response->assertSessionHasErrors('check_in_date');
    }

    public function test_zero_adults_is_rejected(): void
    {
        $response = $this->from(route('guest.reservations.search'))
            ->get(route('guest.reservations.results', [
                'check_in_date' => Carbon::today()->addDays(3)->toDateString(),
                'check_out_date' => Carbon::today()->addDays(6)->toDateString(),
                'adults' => 0,
                'children' => 0,
            ]));

        $response->assertRedirect(route('guest.reservations.search'));
        $response->assertSessionHasErrors('adults');
    }

    public function test_negative_children_is_rejected(): void
    {
        $response = $this->from(route('guest.reservations.search'))
            ->get(route('guest.reservations.results', [
                'check_in_date' => Carbon::today()->addDays(3)->toDateString(),
                'check_out_date' => Carbon::today()->addDays(6)->toDateString(),
                'adults' => 2,
                'children' => -1,
            ]));

        $response->assertRedirect(route('guest.reservations.search'));
        $response->assertSessionHasErrors('children');
    }
}

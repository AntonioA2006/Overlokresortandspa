<?php

namespace Tests\Feature\Guest;

use App\Models\Room;
use App\Models\RoomType;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReservationLocaleTest extends TestCase
{
    use RefreshDatabase;

    public function test_search_page_shows_spanish_content_by_default(): void
    {
        $response = $this->get(route('guest.reservations.search'));

        $response->assertOk();
        $response->assertSee('Encuentra tu estancia');
        $response->assertSee('Llegada');
        $response->assertSee('Buscar disponibilidad');
        $response->assertSee('lang="es"', false);
    }

    public function test_search_page_shows_english_when_locale_is_en(): void
    {
        $response = $this->withSession(['locale' => 'en'])
            ->get(route('guest.reservations.search'));

        $response->assertOk();
        $response->assertSee('Find your stay');
        $response->assertSee('Check-in');
        $response->assertSee('Check-out');
        $response->assertSee('Adults');
        $response->assertSee('Children');
        $response->assertSee('Check availability');
        $response->assertSee('lang="en"', false);
    }

    public function test_locale_switch_route_sets_session_and_redirects(): void
    {
        $response = $this->get(route('locale.switch', [
            'locale' => 'en',
            'redirect' => route('guest.reservations.search'),
        ]));

        $response->assertRedirect(route('guest.reservations.search'));
        $response->assertSessionHas('locale', 'en');
    }

    public function test_language_switcher_is_visible_in_header(): void
    {
        $response = $this->get(route('guest.reservations.search'));

        $response->assertOk();
        $response->assertSee('language-switcher', false);
        $response->assertSee('ES');
        $response->assertSee('EN');
    }

    public function test_validation_messages_are_translated_in_english(): void
    {
        $response = $this->withSession(['locale' => 'en'])
            ->from(route('guest.reservations.search'))
            ->get(route('guest.reservations.results', [
                'check_in_date' => Carbon::today()->addDays(5)->toDateString(),
                'check_out_date' => Carbon::today()->addDays(3)->toDateString(),
                'adults' => 2,
                'children' => 0,
            ]));

        $response->assertRedirect(route('guest.reservations.search'));
        $response->assertSessionHasErrors([
            'check_out_date' => 'The check-out date must be after the check-in date.',
        ]);
    }

    public function test_results_empty_state_is_translated_in_english(): void
    {
        $response = $this->withSession(['locale' => 'en'])
            ->get(route('guest.reservations.results', [
                'check_in_date' => Carbon::today()->addDays(30)->toDateString(),
                'check_out_date' => Carbon::today()->addDays(33)->toDateString(),
                'adults' => 2,
                'children' => 0,
            ]));

        $response->assertOk();
        $response->assertSee('No rooms available');
        $response->assertSee('We couldn\'t find available rooms for these dates.');
        $response->assertSee('Change dates');
        $response->assertSee('2 adults');
        $response->assertSee('0 children');
    }

    public function test_results_page_shows_english_content_with_available_rooms(): void
    {
        $roomType = RoomType::factory()->create(['name' => 'Deluxe Garden']);
        Room::factory()->for($roomType)->create();

        $response = $this->withSession(['locale' => 'en'])
            ->get(route('guest.reservations.results', [
                'check_in_date' => Carbon::today()->addDays(3)->toDateString(),
                'check_out_date' => Carbon::today()->addDays(6)->toDateString(),
                'adults' => 2,
                'children' => 0,
            ]));

        $response->assertOk();
        $response->assertSee('Available rooms');
        $response->assertSee('View room');
        $response->assertSee('Up to');
    }

    public function test_home_page_shows_english_when_locale_is_en(): void
    {
        $response = $this->withSession(['locale' => 'en'])
            ->get(route('home'));

        $response->assertOk();
        $response->assertSee('A stay that stays with you.');
        $response->assertSee('Book a stay');
        $response->assertSee('lang="en"', false);
    }

    public function test_locale_switch_preserves_search_query_on_results_page(): void
    {
        $resultsUrl = route('guest.reservations.results', [
            'check_in_date' => Carbon::today()->addDays(3)->toDateString(),
            'check_out_date' => Carbon::today()->addDays(6)->toDateString(),
            'adults' => 2,
            'children' => 1,
        ]);

        $response = $this->get(route('locale.switch', [
            'locale' => 'en',
            'redirect' => $resultsUrl,
        ]));

        $response->assertRedirect($resultsUrl);
        $response->assertSessionHas('locale', 'en');
    }
}

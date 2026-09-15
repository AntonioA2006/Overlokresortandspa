<?php

namespace Tests\Feature\Reception;

use App\Enums\ReservationStatus;
use App\Enums\UserRole;
use App\Models\CheckIn;
use App\Models\Reservation;
use App\Models\Room;
use App\Models\RoomType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CheckInFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_unauthenticated_request_redirects_to_login(): void
    {
        $response = $this->get(route('reception.dashboard'));

        $response->assertRedirect(route('login'));
    }

    public function test_guest_is_forbidden_from_reception(): void
    {
        $guest = User::factory()->create(['role' => UserRole::Guest]);

        $response = $this->actingAs($guest)->get(route('reception.dashboard'));

        $response->assertForbidden();
    }

    public function test_reception_can_open_reservation_by_token_and_complete_check_in(): void
    {
        $receptionist = User::factory()->create(['role' => UserRole::Reception]);
        $guest = User::factory()->create(['role' => UserRole::Guest, 'name' => 'Ana Huésped']);
        $room = Room::factory()->for(RoomType::factory()->create(['name' => 'Garden Suite']))->create(['number' => '204']);
        $reservation = Reservation::factory()->for($guest)->for($room)->create([
            'status' => ReservationStatus::Confirmed,
            'check_in_date' => now()->toDateString(),
            'check_out_date' => now()->addDays(2)->toDateString(),
            'check_in_token' => 'secure-token-check-in-flow',
        ]);

        $show = $this->actingAs($receptionist)->get(route('reception.check', 'secure-token-check-in-flow'));

        $show->assertSee($reservation->code);
        $show->assertSee('Ana Huésped');
        $show->assertSee('204');
        $this->assertNotNull($reservation->fresh()->checkIn?->qr_scanned_at);

        $this->actingAs($receptionist)
            ->from(route('reception.check', 'secure-token-check-in-flow'))
            ->post(route('reception.check.verify', 'secure-token-check-in-flow'))
            ->assertRedirect(route('reception.check', 'secure-token-check-in-flow'));

        $this->assertNotNull(CheckIn::query()->where('reservation_id', $reservation->id)->first()?->identity_verified_at);

        $this->actingAs($receptionist)
            ->post(route('reception.check.complete', 'secure-token-check-in-flow'), [
                'notes' => 'ID verificada',
            ])
            ->assertRedirect();

        $reservation->refresh();
        $this->assertSame(ReservationStatus::CheckedIn, $reservation->status);
        $this->assertNotNull($reservation->checkIn?->room_delivered_at);
        $this->assertSame('ID verificada', $reservation->checkIn?->notes);
    }

    public function test_unknown_token_redirects_back_to_dashboard(): void
    {
        $receptionist = User::factory()->create(['role' => UserRole::Reception]);

        $response = $this->actingAs($receptionist)->get(route('reception.check', 'missing-token'));

        $response->assertRedirect(route('reception.dashboard'));
        $response->assertSessionHas('reception_error');
    }

    public function test_scan_page_is_available_to_reception(): void
    {
        $receptionist = User::factory()->create(['role' => UserRole::Reception]);

        $response = $this->actingAs($receptionist)->get(route('reception.scan'));

        $response->assertSee('Escaneo de QR');
        $response->assertDontSee('siguiente iteración');
    }
}

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
        $response->assertSee('Activar cámara');
        $response->assertSee('Token o código OVL');
        $response->assertSee(route('reception.lookup'), false);
        $response->assertDontSee('siguiente iteración');
    }

    public function test_dashboard_promotes_qr_scan_as_the_primary_action(): void
    {
        $receptionist = User::factory()->create(['role' => UserRole::Reception]);

        $this->actingAs($receptionist)
            ->get(route('reception.dashboard'))
            ->assertSee('Escanear QR')
            ->assertSee(route('reception.scan'), false)
            ->assertSee(route('reception.lookup'), false);
    }

    public function test_lookup_form_opens_reservation_by_code_without_recording_a_qr_scan(): void
    {
        $receptionist = User::factory()->create(['role' => UserRole::Reception]);
        $reservation = $this->confirmedReservation('lookup-token-by-code');

        $this->actingAs($receptionist)
            ->from(route('reception.scan'))
            ->post(route('reception.lookup'), [
                'lookup' => $reservation->code,
            ])
            ->assertRedirect(route('reception.check', $reservation->code));

        $this->actingAs($receptionist)
            ->get(route('reception.check', $reservation->code))
            ->assertSee($reservation->code)
            ->assertSee('Ana Huésped');

        $this->assertNull($reservation->fresh()->checkIn?->qr_scanned_at);
    }

    public function test_lookup_form_opens_reservation_from_a_qr_url_and_records_the_scan(): void
    {
        $receptionist = User::factory()->create(['role' => UserRole::Reception]);
        $reservation = $this->confirmedReservation('camera-scanned-token');
        $qrUrl = url('/reception/check/camera-scanned-token');

        $this->actingAs($receptionist)
            ->from(route('reception.scan'))
            ->post(route('reception.lookup'), [
                'lookup' => $qrUrl,
            ])
            ->assertRedirect(route('reception.check', 'camera-scanned-token'));

        $this->actingAs($receptionist)
            ->get(route('reception.check', 'camera-scanned-token'))
            ->assertSee('Ana Huésped');

        $this->assertNotNull($reservation->fresh()->checkIn?->qr_scanned_at);
    }

    public function test_unknown_lookup_from_scan_stays_on_the_scan_page(): void
    {
        $receptionist = User::factory()->create(['role' => UserRole::Reception]);

        $this->actingAs($receptionist)
            ->from(route('reception.scan'))
            ->post(route('reception.lookup'), [
                'lookup' => 'missing-token',
            ])
            ->assertRedirect(route('reception.scan'))
            ->assertSessionHas('reception_error');
    }

    public function test_unknown_token_from_scan_page_redirects_back_to_scan(): void
    {
        $receptionist = User::factory()->create(['role' => UserRole::Reception]);

        $this->actingAs($receptionist)
            ->from(route('reception.scan'))
            ->get(route('reception.check', 'missing-token'))
            ->assertRedirect(route('reception.scan'));
    }

    private function confirmedReservation(string $token): Reservation
    {
        $guest = User::factory()->create(['role' => UserRole::Guest, 'name' => 'Ana Huésped']);
        $room = Room::factory()->for(RoomType::factory()->create(['name' => 'Garden Suite']))->create(['number' => '204']);

        return Reservation::factory()->for($guest)->for($room)->create([
            'status' => ReservationStatus::Confirmed,
            'check_in_date' => now()->toDateString(),
            'check_out_date' => now()->addDays(2)->toDateString(),
            'check_in_token' => $token,
        ]);
    }
}

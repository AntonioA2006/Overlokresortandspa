<?php

namespace Tests\Feature\Guest;

use App\Enums\AuditAction;
use App\Enums\ReservationStatus;
use App\Enums\UserRole;
use App\Models\Reservation;
use App\Models\Room;
use App\Models\RoomType;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReservationMyStaysTest extends TestCase
{
    use RefreshDatabase;

    public function test_index_groups_reservations_by_current_upcoming_and_past(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-09-14'));

        $user = User::factory()->create(['role' => UserRole::Guest]);
        $roomType = RoomType::factory()->create(['name' => 'Forest Suite']);
        $room = Room::factory()->for($roomType)->create();

        $current = Reservation::factory()->for($user)->for($room)->create([
            'check_in_date' => '2026-09-12',
            'check_out_date' => '2026-09-16',
            'status' => ReservationStatus::Confirmed,
            'check_in_token' => 'current-token',
        ]);

        $upcoming = Reservation::factory()->for($user)->for($room)->create([
            'check_in_date' => '2026-10-01',
            'check_out_date' => '2026-10-05',
            'status' => ReservationStatus::Confirmed,
        ]);

        $past = Reservation::factory()->for($user)->for($room)->create([
            'check_in_date' => '2026-08-01',
            'check_out_date' => '2026-08-05',
            'status' => ReservationStatus::CheckedOut,
        ]);

        $response = $this->actingAs($user)->get(route('guest.reservations.index'));

        $response->assertOk();
        $response->assertSee(__('reservations.section_current'));
        $response->assertSee(__('reservations.section_upcoming'));
        $response->assertSee(__('reservations.section_past'));
        $response->assertSee($current->code);
        $response->assertSee($upcoming->code);
        $response->assertSee($past->code);
        $response->assertSee('Forest Suite');
    }

    public function test_index_shows_empty_state_when_guest_has_no_reservations(): void
    {
        $user = User::factory()->create(['role' => UserRole::Guest]);

        $response = $this->actingAs($user)->get(route('guest.reservations.index'));

        $response->assertOk();
        $response->assertSee(__('reservations.empty_title'));
        $response->assertSee(__('reservations.search_cta'));
    }

    public function test_guest_cannot_view_another_users_reservation(): void
    {
        $guest = User::factory()->create(['role' => UserRole::Guest]);
        $otherGuest = User::factory()->create(['role' => UserRole::Guest]);
        $reservation = Reservation::factory()->for($otherGuest)->create();

        $this->actingAs($guest)
            ->get(route('guest.reservations.show', $reservation))
            ->assertForbidden();
    }

    public function test_guest_can_cancel_upcoming_confirmed_reservation(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-09-14'));

        $user = User::factory()->create(['role' => UserRole::Guest]);
        $reservation = Reservation::factory()->for($user)->create([
            'check_in_date' => '2026-09-20',
            'check_out_date' => '2026-09-24',
            'status' => ReservationStatus::Confirmed,
            'check_in_token' => 'cancel-me-token',
        ]);

        $response = $this->actingAs($user)->post(route('guest.reservations.cancel', $reservation));

        $response->assertRedirect(route('guest.reservations.show', $reservation));
        $response->assertSessionHas('status', __('reservations.cancelled_success'));

        $reservation->refresh();
        $this->assertSame(ReservationStatus::Cancelled, $reservation->status);
        $this->assertNotNull($reservation->cancelled_at);
        $this->assertNotNull($reservation->token_revoked_at);
    }

    public function test_cancel_logs_audit_entry(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-09-14'));

        $user = User::factory()->create(['role' => UserRole::Guest]);
        $reservation = Reservation::factory()->for($user)->create([
            'check_in_date' => '2026-09-20',
            'check_out_date' => '2026-09-24',
            'status' => ReservationStatus::Confirmed,
        ]);

        $this->actingAs($user)->post(route('guest.reservations.cancel', $reservation));

        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $user->id,
            'action' => AuditAction::ReservationCancelled->value,
            'auditable_type' => Reservation::class,
            'auditable_id' => $reservation->id,
        ]);
    }

    public function test_guest_cannot_cancel_reservation_on_check_in_day(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-09-20'));

        $user = User::factory()->create(['role' => UserRole::Guest]);
        $reservation = Reservation::factory()->for($user)->create([
            'check_in_date' => '2026-09-20',
            'check_out_date' => '2026-09-24',
            'status' => ReservationStatus::Confirmed,
        ]);

        $this->actingAs($user)
            ->post(route('guest.reservations.cancel', $reservation))
            ->assertForbidden();

        $this->assertSame(ReservationStatus::Confirmed, $reservation->fresh()->status);
    }

    public function test_guest_cannot_cancel_another_users_reservation(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-09-14'));

        $guest = User::factory()->create(['role' => UserRole::Guest]);
        $otherGuest = User::factory()->create(['role' => UserRole::Guest]);
        $reservation = Reservation::factory()->for($otherGuest)->create([
            'check_in_date' => '2026-09-20',
            'check_out_date' => '2026-09-24',
            'status' => ReservationStatus::Confirmed,
        ]);

        $this->actingAs($guest)
            ->post(route('guest.reservations.cancel', $reservation))
            ->assertForbidden();
    }

    public function test_show_displays_cancel_action_for_upcoming_reservation(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-09-14'));

        $user = User::factory()->create(['role' => UserRole::Guest]);
        $reservation = Reservation::factory()->for($user)->create([
            'check_in_date' => '2026-09-20',
            'check_out_date' => '2026-09-24',
            'status' => ReservationStatus::Confirmed,
            'check_in_token' => 'show-cancel-token',
        ]);

        $response = $this->actingAs($user)->get(route('guest.reservations.show', $reservation));

        $response->assertOk();
        $response->assertSee(__('reservations.cancel_reservation'));
        $response->assertSee(route('guest.reservations.cancel', $reservation), false);
    }

    public function test_my_stays_page_is_translated_in_english(): void
    {
        $user = User::factory()->create(['role' => UserRole::Guest]);
        Reservation::factory()->for($user)->create([
            'check_in_date' => Carbon::today()->addDays(10)->toDateString(),
            'check_out_date' => Carbon::today()->addDays(13)->toDateString(),
            'status' => ReservationStatus::Confirmed,
        ]);

        $response = $this->withSession(['locale' => 'en'])
            ->actingAs($user)
            ->get(route('guest.reservations.index'));

        $response->assertOk();
        $response->assertSee('My stays');
        $response->assertSee('Upcoming stays');
    }
}

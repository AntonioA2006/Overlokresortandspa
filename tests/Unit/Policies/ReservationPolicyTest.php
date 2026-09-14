<?php

namespace Tests\Unit\Policies;

use App\Enums\UserRole;
use App\Models\Reservation;
use App\Models\User;
use App\Policies\ReservationPolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReservationPolicyTest extends TestCase
{
    use RefreshDatabase;

    private ReservationPolicy $policy;

    protected function setUp(): void
    {
        parent::setUp();

        $this->policy = new ReservationPolicy;
    }

    public function test_guest_can_create_reservations(): void
    {
        $guest = User::factory()->create(['role' => UserRole::Guest]);

        $this->assertTrue($this->policy->create($guest));
    }

    public function test_guest_can_view_own_reservation_only(): void
    {
        $guest = User::factory()->create(['role' => UserRole::Guest]);
        $other = User::factory()->create(['role' => UserRole::Guest]);
        $reservation = Reservation::factory()->for($guest)->create();

        $this->assertTrue($this->policy->view($guest, $reservation));
        $this->assertFalse($this->policy->view($other, $reservation));
    }
}

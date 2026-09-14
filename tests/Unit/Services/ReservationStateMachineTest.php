<?php

namespace Tests\Unit\Services;

use App\Enums\ReservationStatus;
use App\Models\Reservation;
use App\Services\ReservationStateMachine;
use Illuminate\Foundation\Testing\RefreshDatabase;
use InvalidArgumentException;
use Tests\TestCase;

class ReservationStateMachineTest extends TestCase
{
    use RefreshDatabase;

    private ReservationStateMachine $stateMachine;

    protected function setUp(): void
    {
        parent::setUp();

        $this->stateMachine = app(ReservationStateMachine::class);
    }

    public function test_it_transitions_pending_to_confirmed(): void
    {
        $reservation = Reservation::factory()->create([
            'status' => ReservationStatus::Pending,
            'confirmed_at' => null,
        ]);

        $updated = $this->stateMachine->transition($reservation, ReservationStatus::Confirmed);

        $this->assertSame(ReservationStatus::Confirmed, $updated->status);
        $this->assertNotNull($updated->confirmed_at);
    }

    public function test_it_rejects_invalid_transition(): void
    {
        $reservation = Reservation::factory()->create([
            'status' => ReservationStatus::CheckedOut,
        ]);

        $this->expectException(InvalidArgumentException::class);

        $this->stateMachine->transition($reservation, ReservationStatus::Confirmed);
    }
}

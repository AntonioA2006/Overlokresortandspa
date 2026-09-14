<?php

namespace Tests\Unit\Services;

use App\Models\Reservation;
use App\Services\ReservationTokenService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReservationTokenServiceTest extends TestCase
{
    use RefreshDatabase;

    private ReservationTokenService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = app(ReservationTokenService::class);
    }

    public function test_it_generates_a_unique_active_token(): void
    {
        $reservation = Reservation::factory()->create([
            'check_in_token' => null,
        ]);

        $token = $this->service->generate($reservation);

        $this->assertSame(64, strlen($token));
        $this->assertTrue($reservation->refresh()->isTokenActive());
    }

    public function test_ensure_active_token_reuses_existing_token(): void
    {
        $reservation = Reservation::factory()->create([
            'check_in_token' => str_repeat('a', 64),
            'token_revoked_at' => null,
        ]);

        $token = $this->service->ensureActiveToken($reservation);

        $this->assertSame(str_repeat('a', 64), $token);
    }

    public function test_find_active_by_token_returns_reservation(): void
    {
        $reservation = Reservation::factory()->create([
            'check_in_token' => 'lookup-token',
            'token_revoked_at' => null,
        ]);

        $found = $this->service->findActiveByToken('lookup-token');

        $this->assertNotNull($found);
        $this->assertTrue($found->is($reservation));
    }

    public function test_find_active_by_token_returns_null_for_revoked_token(): void
    {
        Reservation::factory()->create([
            'check_in_token' => 'revoked-token',
            'token_revoked_at' => now(),
        ]);

        $this->assertNull($this->service->findActiveByToken('revoked-token'));
    }
}

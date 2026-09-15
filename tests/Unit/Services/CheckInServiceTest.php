<?php

namespace Tests\Unit\Services;

use App\Enums\ReservationStatus;
use App\Exceptions\CheckInException;
use App\Models\Reservation;
use App\Models\Room;
use App\Models\RoomType;
use App\Models\User;
use App\Services\CheckInService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class CheckInServiceTest extends TestCase
{
    use RefreshDatabase;

    #[DataProvider('lookupValues')]
    public function test_normalize_lookup_extracts_token_or_code(string $raw, string $expected): void
    {
        $service = app(CheckInService::class);

        $this->assertSame($expected, $service->normalizeLookup($raw));
    }

    /**
     * @return array<string, array{0: string, 1: string}>
     */
    public static function lookupValues(): array
    {
        return [
            'plain token' => ['secure-token-value', 'secure-token-value'],
            'ovl code' => ['OVL-1001', 'OVL-1001'],
            'trimmed code' => ['  OVL-2002  ', 'OVL-2002'],
            'full check url' => ['http://localhost:8000/reception/check/secure-token-value', 'secure-token-value'],
            'url with query' => ['https://overlook.test/reception/check/secure-token-value?src=qr', 'secure-token-value'],
            'encoded token in url' => ['http://localhost:8000/reception/check/abc%2Fdef', 'abc/def'],
        ];
    }

    public function test_find_for_reception_accepts_a_qr_check_url(): void
    {
        $guest = User::factory()->create();
        $room = Room::factory()->for(RoomType::factory()->create())->create();
        $reservation = Reservation::factory()->for($guest)->for($room)->create([
            'status' => ReservationStatus::Confirmed,
            'check_in_token' => 'camera-token-from-qr',
        ]);
        $service = app(CheckInService::class);

        $found = $service->findForReception('http://localhost:8000/reception/check/camera-token-from-qr');

        $this->assertTrue($found->is($reservation));
    }

    public function test_find_for_reception_rejects_an_empty_lookup(): void
    {
        $this->expectException(CheckInException::class);

        app(CheckInService::class)->findForReception('   ');
    }
}

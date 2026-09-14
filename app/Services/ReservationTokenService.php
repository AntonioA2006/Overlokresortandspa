<?php

namespace App\Services;

use App\Models\Reservation;
use Illuminate\Support\Str;

class ReservationTokenService
{
    public function generate(Reservation $reservation): string
    {
        $token = Str::random((int) config('overlook.reservation_token_length', 64));

        $reservation->forceFill([
            'check_in_token' => $token,
            'token_revoked_at' => null,
        ])->save();

        return $token;
    }

    public function ensureActiveToken(Reservation $reservation): string
    {
        if ($reservation->isTokenActive()) {
            return $reservation->check_in_token;
        }

        return $this->generate($reservation);
    }

    public function revoke(Reservation $reservation): void
    {
        $reservation->forceFill([
            'token_revoked_at' => now(),
        ])->save();
    }

    public function buildCheckUrl(string $token): string
    {
        return url('/reception/check/'.$token);
    }

    public function findActiveByToken(string $token): ?Reservation
    {
        return Reservation::query()
            ->where('check_in_token', $token)
            ->whereNull('token_revoked_at')
            ->first();
    }
}

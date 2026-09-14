<?php

namespace App\Services;

use App\Enums\AuditAction;
use App\Enums\ReservationStatus;
use App\Exceptions\ReservationCancellationException;
use App\Exceptions\ReservationUnavailableException;
use App\Models\Reservation;
use App\Models\Room;
use App\Models\User;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ReservationService
{
    public function __construct(
        private AvailabilityService $availabilityService,
        private PricingService $pricingService,
        private AuditService $auditService,
        private ReservationStateMachine $stateMachine,
        private ReservationTokenService $tokenService,
    ) {}

    public function buildIdempotencyKey(
        User $user,
        Room $room,
        CarbonInterface $checkIn,
        CarbonInterface $checkOut,
        int $guestsCount,
    ): string {
        return hash('sha256', implode('|', [
            $user->id,
            $room->id,
            $checkIn->toDateString(),
            $checkOut->toDateString(),
            $guestsCount,
        ]));
    }

    public function create(
        User $user,
        Room $room,
        CarbonInterface $checkIn,
        CarbonInterface $checkOut,
        int $guestsCount,
        string $idempotencyKey,
    ): Reservation {
        $existing = Reservation::query()
            ->where('idempotency_key', $idempotencyKey)
            ->where('user_id', $user->id)
            ->first();

        if ($existing !== null) {
            return $this->confirmAndIssueToken($existing->loadMissing(['room.roomType']));
        }

        $reservation = DB::transaction(function () use ($user, $room, $checkIn, $checkOut, $guestsCount, $idempotencyKey): Reservation {
            /** @var Room $lockedRoom */
            $lockedRoom = Room::query()
                ->whereKey($room->id)
                ->lockForUpdate()
                ->firstOrFail();

            $lockedRoom->loadMissing('roomType');

            if ($lockedRoom->roomType === null
                || ! $lockedRoom->roomType->is_active
                || $lockedRoom->roomType->max_guests < $guestsCount) {
                throw new ReservationUnavailableException;
            }

            if (! $this->availabilityService->isRoomAvailable($lockedRoom, $checkIn, $checkOut, $guestsCount)) {
                throw new ReservationUnavailableException;
            }

            $pricing = $this->pricingService->calculateTotal($lockedRoom, $checkIn, $checkOut);

            $reservation = Reservation::query()->create([
                'code' => $this->generateUniqueCode(),
                'user_id' => $user->id,
                'room_id' => $lockedRoom->id,
                'check_in_date' => $checkIn->toDateString(),
                'check_out_date' => $checkOut->toDateString(),
                'guests_count' => $guestsCount,
                'price_total' => $pricing['total'],
                'status' => ReservationStatus::Pending,
                'idempotency_key' => $idempotencyKey,
            ]);

            $this->auditService->log(
                AuditAction::ReservationCreated,
                $reservation,
                [
                    'room_id' => $lockedRoom->id,
                    'room_type' => $lockedRoom->roomType->name,
                    'guests_count' => $guestsCount,
                    'price_total' => $pricing['total'],
                    'currency' => $pricing['currency'],
                ],
            );

            return $reservation;
        });

        return $this->confirmAndIssueToken($reservation);
    }

    public function confirmAndIssueToken(Reservation $reservation): Reservation
    {
        if ($reservation->status === ReservationStatus::Pending) {
            $reservation = $this->stateMachine->transition($reservation, ReservationStatus::Confirmed);

            $this->auditService->log(
                AuditAction::ReservationConfirmed,
                $reservation,
                ['code' => $reservation->code],
            );
        }

        if ($reservation->status === ReservationStatus::Confirmed) {
            $this->tokenService->ensureActiveToken($reservation);
        }

        return $reservation->refresh()->loadMissing(['room.roomType']);
    }

    /**
     * @return array{current: Collection<int, Reservation>, upcoming: Collection<int, Reservation>, past: Collection<int, Reservation>}
     */
    public function groupedForUser(User $user): array
    {
        $today = now()->startOfDay();

        $reservations = Reservation::query()
            ->where('user_id', $user->id)
            ->with(['room.roomType', 'room.photos'])
            ->orderByDesc('check_in_date')
            ->get();

        $current = collect();
        $upcoming = collect();
        $past = collect();

        foreach ($reservations as $reservation) {
            match ($this->resolveStayGroup($reservation, $today)) {
                'current' => $current->push($reservation),
                'upcoming' => $upcoming->push($reservation),
                default => $past->push($reservation),
            };
        }

        return [
            'current' => $current->sortBy('check_in_date')->values(),
            'upcoming' => $upcoming->sortBy('check_in_date')->values(),
            'past' => $past->sortByDesc('check_in_date')->values(),
        ];
    }

    public function cancel(User $user, Reservation $reservation): Reservation
    {
        if ($user->id !== $reservation->user_id) {
            throw new ReservationCancellationException;
        }

        return DB::transaction(function () use ($reservation): Reservation {
            /** @var Reservation $lockedReservation */
            $lockedReservation = Reservation::query()
                ->whereKey($reservation->id)
                ->lockForUpdate()
                ->firstOrFail();

            if (! $lockedReservation->status->canTransitionTo(ReservationStatus::Cancelled)) {
                throw new ReservationCancellationException;
            }

            if (! $lockedReservation->check_in_date->startOfDay()->gt(now()->startOfDay())) {
                throw new ReservationCancellationException;
            }

            $lockedReservation = $this->stateMachine->transition($lockedReservation, ReservationStatus::Cancelled);
            $this->tokenService->revoke($lockedReservation);

            $this->auditService->log(
                AuditAction::ReservationCancelled,
                $lockedReservation,
                ['code' => $lockedReservation->code],
            );

            return $lockedReservation->refresh()->loadMissing(['room.roomType']);
        });
    }

    private function resolveStayGroup(Reservation $reservation, CarbonInterface $today): string
    {
        if (in_array($reservation->status, [
            ReservationStatus::Cancelled,
            ReservationStatus::CheckedOut,
            ReservationStatus::NoShow,
        ], true)) {
            return 'past';
        }

        $checkIn = $reservation->check_in_date->startOfDay();
        $checkOut = $reservation->check_out_date->startOfDay();

        if ($checkIn->lte($today)
            && $checkOut->gt($today)
            && in_array($reservation->status, [ReservationStatus::Confirmed, ReservationStatus::CheckedIn], true)) {
            return 'current';
        }

        if ($checkIn->gt($today)
            && in_array($reservation->status, [ReservationStatus::Pending, ReservationStatus::Confirmed], true)) {
            return 'upcoming';
        }

        return 'past';
    }

    private function generateUniqueCode(): string
    {
        $prefix = (string) config('overlook.reservation_code_prefix', 'OVL');

        do {
            $code = $prefix.'-'.Str::upper(Str::random(8));
        } while (Reservation::query()->where('code', $code)->exists());

        return $code;
    }
}

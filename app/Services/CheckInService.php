<?php

namespace App\Services;

use App\Enums\AuditAction;
use App\Enums\ReservationStatus;
use App\Enums\UserRole;
use App\Exceptions\CheckInException;
use App\Models\CheckIn;
use App\Models\Reservation;
use App\Models\User;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CheckInService
{
    public function __construct(
        private AuditService $auditService,
        private ReservationStateMachine $stateMachine,
        private ReservationTokenService $tokenService,
        private NotificationService $notificationService,
    ) {}

    public function findForReception(string $lookup): Reservation
    {
        $lookup = trim($lookup);

        if ($lookup === '') {
            throw new CheckInException(__('reception.errors.token_invalid'));
        }

        $reservation = $this->tokenService->findActiveByToken($lookup);

        if ($reservation === null) {
            $reservation = Reservation::query()
                ->where('code', Str::upper($lookup))
                ->first();
        }

        if ($reservation === null) {
            throw new CheckInException(__('reception.errors.token_invalid'));
        }

        return $reservation->loadMissing(['user', 'room.roomType', 'checkIn']);
    }

    public function recordScan(User $receptionist, Reservation $reservation): CheckIn
    {
        $this->assertStaff($receptionist);

        return DB::transaction(function () use ($receptionist, $reservation): CheckIn {
            $lockedReservation = $this->lockReservation($reservation);
            $checkIn = $this->lockOrCreateCheckIn($lockedReservation, $receptionist);

            if ($checkIn->qr_scanned_at === null) {
                $checkIn->forceFill([
                    'qr_scanned_at' => now(),
                    'receptionist_id' => $receptionist->id,
                ])->save();

                $this->auditService->log(AuditAction::QrScanned, $lockedReservation, [
                    'code' => $lockedReservation->code,
                ]);
            }

            return $checkIn->refresh();
        });
    }

    public function verifyIdentity(User $receptionist, Reservation $reservation): CheckIn
    {
        $this->assertStaff($receptionist);

        return DB::transaction(function () use ($receptionist, $reservation): CheckIn {
            $lockedReservation = $this->lockReservation($reservation);
            $checkIn = $this->lockOrCreateCheckIn($lockedReservation, $receptionist);

            if ($checkIn->qr_scanned_at === null) {
                $checkIn->qr_scanned_at = now();
            }

            if ($checkIn->identity_verified_at === null) {
                $checkIn->forceFill([
                    'identity_verified_at' => now(),
                    'receptionist_id' => $receptionist->id,
                ])->save();

                $this->auditService->log(AuditAction::IdentityVerified, $lockedReservation, [
                    'code' => $lockedReservation->code,
                    'guest_id' => $lockedReservation->user_id,
                ]);
            }

            return $checkIn->refresh();
        });
    }

    public function complete(User $receptionist, Reservation $reservation, ?string $notes = null): CheckIn
    {
        $this->assertStaff($receptionist);

        return DB::transaction(function () use ($receptionist, $reservation, $notes): CheckIn {
            $lockedReservation = $this->lockReservation($reservation);
            $checkIn = $this->lockOrCreateCheckIn($lockedReservation, $receptionist);

            if ($checkIn->identity_verified_at === null) {
                throw new CheckInException(__('reception.errors.identity_required'));
            }

            if ($lockedReservation->status === ReservationStatus::Confirmed) {
                if (! $lockedReservation->status->canTransitionTo(ReservationStatus::CheckedIn)) {
                    throw new CheckInException(__('reception.errors.check_in_not_allowed'));
                }

                $this->stateMachine->transition($lockedReservation, ReservationStatus::CheckedIn);
            }

            if ($lockedReservation->status !== ReservationStatus::CheckedIn) {
                throw new CheckInException(__('reception.errors.check_in_not_allowed'));
            }

            $checkIn->forceFill([
                'receptionist_id' => $receptionist->id,
                'room_delivered_at' => $checkIn->room_delivered_at ?? now(),
                'notes' => $notes ?? $checkIn->notes,
            ])->save();

            $this->auditService->log(AuditAction::CheckInCompleted, $lockedReservation, [
                'code' => $lockedReservation->code,
            ]);
            $this->auditService->log(AuditAction::RoomDelivered, $lockedReservation, [
                'room_id' => $lockedReservation->room_id,
            ]);

            $this->notificationService->notifyRoomDelivered($lockedReservation->refresh());

            return $checkIn->refresh();
        });
    }

    public function checkout(User $receptionist, Reservation $reservation): Reservation
    {
        $this->assertStaff($receptionist);

        return DB::transaction(function () use ($reservation): Reservation {
            $lockedReservation = $this->lockReservation($reservation);

            if (! $lockedReservation->status->canTransitionTo(ReservationStatus::CheckedOut)) {
                throw new CheckInException(__('reception.errors.checkout_not_allowed'));
            }

            $lockedReservation = $this->stateMachine->transition($lockedReservation, ReservationStatus::CheckedOut);
            $this->tokenService->revoke($lockedReservation);

            $this->auditService->log(AuditAction::CheckOutCompleted, $lockedReservation, [
                'code' => $lockedReservation->code,
            ]);

            return $lockedReservation->refresh()->loadMissing(['user', 'room.roomType', 'checkIn']);
        });
    }

    /**
     * @return Collection<int, Reservation>
     */
    public function arrivalsForDate(CarbonInterface $date): Collection
    {
        return Reservation::query()
            ->with(['user', 'room.roomType', 'checkIn'])
            ->whereDate('check_in_date', $date->toDateString())
            ->whereIn('status', [
                ReservationStatus::Confirmed,
                ReservationStatus::CheckedIn,
            ])
            ->orderBy('status')
            ->orderBy('code')
            ->get();
    }

    private function lockReservation(Reservation $reservation): Reservation
    {
        /** @var Reservation $lockedReservation */
        $lockedReservation = Reservation::query()
            ->whereKey($reservation->id)
            ->lockForUpdate()
            ->firstOrFail();

        return $lockedReservation->loadMissing(['user', 'room.roomType', 'checkIn']);
    }

    private function lockOrCreateCheckIn(Reservation $reservation, User $receptionist): CheckIn
    {
        $checkIn = CheckIn::query()
            ->where('reservation_id', $reservation->id)
            ->lockForUpdate()
            ->first();

        if ($checkIn !== null) {
            return $checkIn;
        }

        return CheckIn::query()->create([
            'reservation_id' => $reservation->id,
            'receptionist_id' => $receptionist->id,
        ]);
    }

    private function assertStaff(User $user): void
    {
        if (! $user->hasRole(UserRole::Reception, UserRole::Admin)) {
            throw new CheckInException(__('reception.errors.forbidden'));
        }
    }
}

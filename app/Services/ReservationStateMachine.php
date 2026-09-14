<?php

namespace App\Services;

use App\Enums\ReservationStatus;
use App\Models\Reservation;
use InvalidArgumentException;

class ReservationStateMachine
{
    public function transition(Reservation $reservation, ReservationStatus $target): Reservation
    {
        $current = $reservation->status;

        if (! $current->canTransitionTo($target)) {
            throw new InvalidArgumentException(
                sprintf(
                    'Transición inválida de [%s] a [%s] para la reservación %s.',
                    $current->value,
                    $target->value,
                    $reservation->code
                )
            );
        }

        $reservation->status = $target;

        if ($target === ReservationStatus::Confirmed && $reservation->confirmed_at === null) {
            $reservation->confirmed_at = now();
        }

        if ($target === ReservationStatus::Cancelled && $reservation->cancelled_at === null) {
            $reservation->cancelled_at = now();
        }

        $reservation->save();

        return $reservation->refresh();
    }
}

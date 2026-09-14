<?php

namespace App\Policies;

use App\Enums\ReservationStatus;
use App\Enums\UserRole;
use App\Models\Reservation;
use App\Models\User;

class ReservationPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasRole(UserRole::Guest, UserRole::Reception, UserRole::Admin);
    }

    public function view(User $user, Reservation $reservation): bool
    {
        if ($user->hasRole(UserRole::Reception, UserRole::Admin)) {
            return true;
        }

        return $user->id === $reservation->user_id;
    }

    public function create(User $user): bool
    {
        return $user->hasRole(UserRole::Guest, UserRole::Admin);
    }

    public function update(User $user, Reservation $reservation): bool
    {
        return $user->hasRole(UserRole::Admin)
            || ($user->id === $reservation->user_id && $user->isGuest());
    }

    public function cancel(User $user, Reservation $reservation): bool
    {
        if ($user->id !== $reservation->user_id || ! $user->isGuest()) {
            return false;
        }

        if (! $reservation->status->canTransitionTo(ReservationStatus::Cancelled)) {
            return false;
        }

        return $reservation->check_in_date->startOfDay()->gt(now()->startOfDay());
    }

    public function checkIn(User $user, Reservation $reservation): bool
    {
        return $user->hasRole(UserRole::Reception, UserRole::Admin);
    }
}

<?php

namespace App\Policies;

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
        return $this->update($user, $reservation);
    }

    public function checkIn(User $user, Reservation $reservation): bool
    {
        return $user->hasRole(UserRole::Reception, UserRole::Admin);
    }
}

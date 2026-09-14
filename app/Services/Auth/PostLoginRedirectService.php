<?php

namespace App\Services\Auth;

use App\Enums\UserRole;
use App\Models\User;

class PostLoginRedirectService
{
    public function redirectPath(User $user): string
    {
        return match ($user->role) {
            UserRole::Admin => route('admin.dashboard'),
            UserRole::Reception => route('reception.dashboard'),
            UserRole::Support => route('support.dashboard'),
            UserRole::Guest => route('guest.reservations.index'),
        };
    }
}

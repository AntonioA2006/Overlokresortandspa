<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\RoomType;
use App\Models\User;

class RoomTypePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasRole(UserRole::Admin);
    }

    public function view(User $user, RoomType $roomType): bool
    {
        return $user->hasRole(UserRole::Admin);
    }

    public function create(User $user): bool
    {
        return $user->hasRole(UserRole::Admin);
    }

    public function update(User $user, RoomType $roomType): bool
    {
        return $user->hasRole(UserRole::Admin);
    }
}

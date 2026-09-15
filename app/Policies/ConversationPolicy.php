<?php

namespace App\Policies;

use App\Enums\ConversationStatus;
use App\Enums\UserRole;
use App\Models\Conversation;
use App\Models\User;

class ConversationPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasRole(UserRole::Support, UserRole::Admin);
    }

    public function view(User $user, Conversation $conversation): bool
    {
        if ($user->hasRole(UserRole::Support, UserRole::Admin)) {
            return true;
        }

        return $user->id === $conversation->user_id;
    }

    public function reply(User $user, Conversation $conversation): bool
    {
        if ($conversation->status === ConversationStatus::Closed) {
            return false;
        }

        if ($user->hasRole(UserRole::Support, UserRole::Admin)) {
            return true;
        }

        return $user->id === $conversation->user_id;
    }

    public function close(User $user, Conversation $conversation): bool
    {
        return $user->hasRole(UserRole::Support, UserRole::Admin)
            && $conversation->status !== ConversationStatus::Closed;
    }
}

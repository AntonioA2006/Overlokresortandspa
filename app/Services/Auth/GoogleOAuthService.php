<?php

namespace App\Services\Auth;

use App\Enums\AuthProvider;
use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Laravel\Socialite\Contracts\User as SocialiteUser;
use RuntimeException;

class GoogleOAuthService
{
    public function resolveAuthenticatedUser(SocialiteUser $googleUser): User
    {
        $providerId = $googleUser->getId();
        $email = $googleUser->getEmail();

        if ($providerId === null || $providerId === '') {
            throw new RuntimeException('Google no proporcionó un identificador de usuario válido.');
        }

        if ($email === null || $email === '') {
            throw new RuntimeException('Google no proporcionó un correo electrónico válido.');
        }

        $user = User::query()
            ->where('auth_provider', AuthProvider::Google)
            ->where('auth_provider_id', $providerId)
            ->first();

        if ($user !== null) {
            return $this->syncProfile($user, $googleUser);
        }

        $existingUser = User::query()->where('email', $email)->first();

        if ($existingUser !== null) {
            return $this->linkGoogleAccount($existingUser, $googleUser);
        }

        return $this->createGuestUser($googleUser);
    }

    private function linkGoogleAccount(User $user, SocialiteUser $googleUser): User
    {
        if ($user->auth_provider !== null
            && $user->auth_provider_id !== null
            && ($user->auth_provider !== AuthProvider::Google
                || $user->auth_provider_id !== $googleUser->getId())) {
            Log::warning('Intento de vincular Google a una cuenta con otro proveedor OAuth.', [
                'user_id' => $user->id,
                'existing_provider' => $user->auth_provider?->value,
            ]);

            throw new RuntimeException('Esta cuenta ya está vinculada a otro método de autenticación.');
        }

        $duplicateProviderAccount = User::query()
            ->where('auth_provider', AuthProvider::Google)
            ->where('auth_provider_id', $googleUser->getId())
            ->whereKeyNot($user->id)
            ->exists();

        if ($duplicateProviderAccount) {
            throw new RuntimeException('Esta cuenta de Google ya está vinculada a otro usuario.');
        }

        $user->fill([
            'auth_provider' => AuthProvider::Google,
            'auth_provider_id' => $googleUser->getId(),
            'avatar' => $googleUser->getAvatar(),
        ]);

        if ($user->email_verified_at === null) {
            $user->email_verified_at = now();
        }

        $user->save();

        return $user->refresh();
    }

    private function createGuestUser(SocialiteUser $googleUser): User
    {
        return DB::transaction(function () use ($googleUser): User {
            return User::query()->create([
                'name' => $googleUser->getName() ?? 'Huésped Overlook',
                'email' => $googleUser->getEmail(),
                'password' => null,
                'role' => UserRole::Guest,
                'auth_provider' => AuthProvider::Google,
                'auth_provider_id' => $googleUser->getId(),
                'avatar' => $googleUser->getAvatar(),
                'email_verified_at' => now(),
            ]);
        });
    }

    private function syncProfile(User $user, SocialiteUser $googleUser): User
    {
        $user->fill([
            'name' => $googleUser->getName() ?? $user->name,
            'avatar' => $googleUser->getAvatar(),
        ]);

        if ($user->email_verified_at === null) {
            $user->email_verified_at = now();
        }

        $user->save();

        return $user->refresh();
    }
}

<?php

namespace Tests\Unit\Services;

use App\Enums\AuthProvider;
use App\Enums\UserRole;
use App\Models\User;
use App\Services\Auth\GoogleOAuthService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\MocksGoogleSocialiteUser;
use Tests\TestCase;

class GoogleOAuthServiceTest extends TestCase
{
    use MocksGoogleSocialiteUser;
    use RefreshDatabase;

    private GoogleOAuthService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = app(GoogleOAuthService::class);
    }

    public function test_it_creates_a_guest_user_for_new_google_account(): void
    {
        $googleUser = $this->makeGoogleSocialiteUser([
            'id' => 'google-new-user',
            'email' => 'newguest@gmail.com',
            'name' => 'Nuevo Huésped',
        ]);

        $user = $this->service->resolveAuthenticatedUser($googleUser);

        $this->assertDatabaseHas('users', [
            'email' => 'newguest@gmail.com',
            'role' => UserRole::Guest->value,
            'auth_provider' => AuthProvider::Google->value,
            'auth_provider_id' => 'google-new-user',
        ]);
        $this->assertSame(UserRole::Guest, $user->role);
        $this->assertNotNull($user->email_verified_at);
    }

    public function test_it_logs_in_existing_user_by_email_without_duplicating_account(): void
    {
        $existing = User::factory()->create([
            'email' => 'existing@gmail.com',
            'role' => UserRole::Guest,
            'auth_provider' => null,
            'auth_provider_id' => null,
        ]);

        $googleUser = $this->makeGoogleSocialiteUser([
            'id' => 'google-existing-user',
            'email' => 'existing@gmail.com',
        ]);

        $user = $this->service->resolveAuthenticatedUser($googleUser);

        $this->assertSame($existing->id, $user->id);
        $this->assertSame(1, User::query()->where('email', 'existing@gmail.com')->count());
        $this->assertSame(AuthProvider::Google, $user->auth_provider);
        $this->assertSame('google-existing-user', $user->auth_provider_id);
    }

    public function test_it_preserves_internal_role_when_linking_google_to_existing_account(): void
    {
        $existing = User::factory()->create([
            'email' => 'reception@gmail.com',
            'role' => UserRole::Reception,
            'auth_provider' => null,
            'auth_provider_id' => null,
        ]);

        $googleUser = $this->makeGoogleSocialiteUser([
            'id' => 'google-reception-user',
            'email' => 'reception@gmail.com',
            'name' => 'Recepción Google',
        ]);

        $user = $this->service->resolveAuthenticatedUser($googleUser);

        $this->assertSame($existing->id, $user->id);
        $this->assertSame(UserRole::Reception, $user->role);
    }

    public function test_it_finds_user_by_provider_identity_on_subsequent_logins(): void
    {
        User::factory()->create([
            'email' => 'linked@gmail.com',
            'role' => UserRole::Guest,
            'auth_provider' => AuthProvider::Google,
            'auth_provider_id' => 'google-linked-user',
        ]);

        $googleUser = $this->makeGoogleSocialiteUser([
            'id' => 'google-linked-user',
            'email' => 'linked@gmail.com',
        ]);

        $user = $this->service->resolveAuthenticatedUser($googleUser);

        $this->assertSame(1, User::query()->where('email', 'linked@gmail.com')->count());
        $this->assertSame('linked@gmail.com', $user->email);
    }
}

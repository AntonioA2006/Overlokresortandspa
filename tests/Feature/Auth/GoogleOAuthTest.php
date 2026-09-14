<?php

namespace Tests\Feature\Auth;

use App\Enums\AuthProvider;
use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Socialite\Contracts\Provider;
use Laravel\Socialite\Facades\Socialite;
use Mockery;
use Tests\Concerns\MocksGoogleSocialiteUser;
use Tests\TestCase;

class GoogleOAuthTest extends TestCase
{
    use MocksGoogleSocialiteUser;
    use RefreshDatabase;

    public function test_login_page_displays_google_button(): void
    {
        $response = $this->get(route('login'));

        $response->assertOk();
        $response->assertSee('Continuar con Google');
        $response->assertSee(route('auth.google.redirect'), false);
    }

    public function test_google_callback_creates_guest_and_logs_in(): void
    {
        $googleUser = $this->makeGoogleSocialiteUser([
            'id' => 'google-callback-new',
            'email' => 'callback-new@gmail.com',
        ]);

        $provider = Mockery::mock(Provider::class);
        $provider->shouldReceive('user')->once()->andReturn($googleUser);

        Socialite::shouldReceive('driver')->once()->with('google')->andReturn($provider);

        $response = $this->get(route('auth.google.callback'));

        $response->assertRedirect(route('guest.reservations.index'));
        $this->assertAuthenticatedAs(User::query()->where('email', 'callback-new@gmail.com')->first());
        $this->assertDatabaseHas('users', [
            'email' => 'callback-new@gmail.com',
            'role' => UserRole::Guest->value,
            'auth_provider' => AuthProvider::Google->value,
        ]);
    }

    public function test_google_callback_logs_in_existing_user_without_duplicating(): void
    {
        $existing = User::factory()->create([
            'email' => 'callback-existing@gmail.com',
            'role' => UserRole::Guest,
        ]);

        $googleUser = $this->makeGoogleSocialiteUser([
            'id' => 'google-callback-existing',
            'email' => 'callback-existing@gmail.com',
        ]);

        $provider = Mockery::mock(Provider::class);
        $provider->shouldReceive('user')->once()->andReturn($googleUser);
        Socialite::shouldReceive('driver')->once()->with('google')->andReturn($provider);

        $response = $this->get(route('auth.google.callback'));

        $response->assertRedirect(route('guest.reservations.index'));
        $this->assertAuthenticatedAs($existing->fresh());
        $this->assertSame(1, User::query()->where('email', 'callback-existing@gmail.com')->count());
    }

    public function test_google_callback_preserves_internal_role(): void
    {
        $receptionist = User::factory()->create([
            'email' => 'callback-reception@gmail.com',
            'role' => UserRole::Reception,
        ]);

        $googleUser = $this->makeGoogleSocialiteUser([
            'id' => 'google-callback-reception',
            'email' => 'callback-reception@gmail.com',
        ]);

        $provider = Mockery::mock(Provider::class);
        $provider->shouldReceive('user')->once()->andReturn($googleUser);
        Socialite::shouldReceive('driver')->once()->with('google')->andReturn($provider);

        $response = $this->get(route('auth.google.callback'));

        $response->assertRedirect(route('reception.dashboard'));
        $this->assertAuthenticatedAs($receptionist->fresh());
        $this->assertSame(UserRole::Reception, auth()->user()->role);
    }

    public function test_oauth_cancel_shows_friendly_message_on_login_page(): void
    {
        $response = $this->get(route('auth.google.callback', ['error' => 'access_denied']));

        $response->assertRedirect(route('login'));
        $response->assertSessionHas('auth_error', 'Cancelaste el inicio de sesión con Google.');
        $this->assertGuest();
    }

    public function test_guest_reservations_requires_authentication(): void
    {
        $response = $this->get(route('guest.reservations.index'));

        $response->assertRedirect(route('login'));
    }

    public function test_guest_cannot_access_reception_panel(): void
    {
        $guest = User::factory()->create([
            'role' => UserRole::Guest,
        ]);

        $response = $this->actingAs($guest)->get(route('reception.dashboard'));

        $response->assertForbidden();
    }

    public function test_logout_invalidates_session(): void
    {
        $user = User::factory()->create([
            'role' => UserRole::Guest,
        ]);

        $response = $this->actingAs($user)->post(route('logout'));

        $response->assertRedirect(route('home'));
        $this->assertGuest();
    }
}

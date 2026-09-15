<?php

namespace Tests\Feature\Auth;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PasswordLoginTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_page_displays_email_form_and_google_button(): void
    {
        $response = $this->get(route('login'));

        $response->assertSee('Continuar con Google');
        $response->assertSee(route('auth.google.redirect'), false);
        $response->assertSee(route('login.store'), false);
        $response->assertSee('Correo electrónico');
    }

    public function test_staff_can_sign_in_with_email_and_password(): void
    {
        $user = User::factory()->create([
            'email' => 'reception@overlook.test',
            'role' => UserRole::Reception,
            'password' => 'password',
        ]);

        $response = $this->post(route('login.store'), [
            'email' => 'reception@overlook.test',
            'password' => 'password',
        ]);

        $response->assertRedirect(route('reception.dashboard'));
        $this->assertAuthenticatedAs($user);
    }

    public function test_guest_is_redirected_to_my_stays_after_password_login(): void
    {
        User::factory()->create([
            'email' => 'guest@overlook.test',
            'role' => UserRole::Guest,
            'password' => 'password',
        ]);

        $response = $this->post(route('login.store'), [
            'email' => 'guest@overlook.test',
            'password' => 'password',
        ]);

        $response->assertRedirect(route('guest.reservations.index'));
        $this->assertAuthenticated();
    }

    public function test_invalid_credentials_are_rejected(): void
    {
        User::factory()->create([
            'email' => 'guest@overlook.test',
            'password' => 'password',
        ]);

        $response = $this->from(route('login'))->post(route('login.store'), [
            'email' => 'guest@overlook.test',
            'password' => 'wrong-password',
        ]);

        $response->assertRedirect(route('login'));
        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_google_only_accounts_cannot_use_password_login(): void
    {
        User::factory()->create([
            'email' => 'oauth@gmail.com',
            'password' => null,
            'role' => UserRole::Guest,
        ]);

        $response = $this->from(route('login'))->post(route('login.store'), [
            'email' => 'oauth@gmail.com',
            'password' => 'password',
        ]);

        $response->assertRedirect(route('login'));
        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }
}

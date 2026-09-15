<?php

namespace Tests\Feature\Auth;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_register_page_is_available(): void
    {
        $this->get(route('register'))
            ->assertSee('Crear cuenta')
            ->assertSee(route('register.store'), false);
    }

    public function test_guest_can_register_with_email_and_password(): void
    {
        $response = $this->post(route('register.store'), [
            'name' => 'Ana Huésped',
            'email' => 'ana@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $response->assertRedirect(route('guest.reservations.index'));
        $this->assertAuthenticated();
        $this->assertDatabaseHas('users', [
            'email' => 'ana@example.com',
            'role' => UserRole::Guest->value,
        ]);
    }

    public function test_registration_ignores_an_injected_admin_role(): void
    {
        $this->post(route('register.store'), [
            'name' => 'Intruso',
            'email' => 'intruso@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'role' => UserRole::Admin->value,
        ])->assertSessionHasErrors('role');

        $this->assertGuest();
        $this->assertDatabaseMissing('users', ['email' => 'intruso@example.com']);
    }

    public function test_duplicate_email_is_rejected(): void
    {
        User::factory()->create(['email' => 'ana@example.com']);

        $this->from(route('register'))->post(route('register.store'), [
            'name' => 'Ana',
            'email' => 'ana@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ])->assertRedirect(route('register'))
            ->assertSessionHasErrors('email');
    }

    public function test_login_page_links_to_register_and_password_recovery(): void
    {
        $this->get(route('login'))
            ->assertSee(route('register'), false)
            ->assertSee(route('password.request'), false);
    }
}

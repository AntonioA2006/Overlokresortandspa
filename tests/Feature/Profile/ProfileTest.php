<?php

namespace Tests\Feature\Profile;

use App\Enums\AuthProvider;
use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_unauthenticated_request_redirects_to_login(): void
    {
        $this->get(route('profile.edit'))->assertRedirect(route('login'));
    }

    public function test_guest_can_update_name_and_email(): void
    {
        $user = User::factory()->create([
            'name' => 'Ana',
            'email' => 'ana@example.com',
            'role' => UserRole::Guest,
        ]);

        $response = $this->actingAs($user)->from(route('profile.edit'))->post(route('profile.update'), [
            'name' => 'Ana López',
            'email' => 'ana.lopez@example.com',
            'phone' => '555-0100',
        ]);

        $response->assertRedirect(route('profile.edit'));
        $response->assertSessionHas('status');
        $this->assertSame('Ana López', $user->fresh()->name);
        $this->assertSame('ana.lopez@example.com', $user->fresh()->email);
        $this->assertSame('555-0100', $user->fresh()->phone);
        $this->assertSame(UserRole::Guest, $user->fresh()->role);
    }

    public function test_profile_rejects_an_injected_role(): void
    {
        $user = User::factory()->create(['role' => UserRole::Guest]);

        $this->actingAs($user)->post(route('profile.update'), [
            'name' => $user->name,
            'email' => $user->email,
            'role' => UserRole::Admin->value,
        ])->assertSessionHasErrors('role');

        $this->assertSame(UserRole::Guest, $user->fresh()->role);
    }

    public function test_password_change_requires_the_current_password(): void
    {
        $user = User::factory()->create(['password' => 'password']);

        $this->actingAs($user)->from(route('profile.edit'))->post(route('profile.password'), [
            'current_password' => 'wrong-password',
            'password' => 'new-password',
            'password_confirmation' => 'new-password',
        ])->assertRedirect(route('profile.edit'))
            ->assertSessionHasErrors('current_password');

        $this->assertTrue(Hash::check('password', $user->fresh()->password));
    }

    public function test_user_with_password_can_change_it(): void
    {
        $user = User::factory()->create(['password' => 'password']);

        $this->actingAs($user)->post(route('profile.password'), [
            'current_password' => 'password',
            'password' => 'new-password',
            'password_confirmation' => 'new-password',
        ])->assertRedirect();

        $this->assertTrue(Hash::check('new-password', $user->fresh()->password));
    }

    public function test_google_only_account_can_set_a_password_without_current_password(): void
    {
        $user = User::factory()->create([
            'password' => null,
            'auth_provider' => AuthProvider::Google,
            'auth_provider_id' => 'google-123',
        ]);

        $this->actingAs($user)->post(route('profile.password'), [
            'password' => 'new-password',
            'password_confirmation' => 'new-password',
        ])->assertRedirect();

        $this->assertTrue($user->fresh()->hasPassword());
        $this->assertTrue(Hash::check('new-password', $user->fresh()->password));
        $this->assertSame(AuthProvider::Google, $user->fresh()->auth_provider);
    }
}

<?php

namespace Tests\Feature\Auth;

use App\Enums\UserRole;
use App\Models\User;
use App\Notifications\ResetPasswordNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use Tests\TestCase;

class PasswordResetTest extends TestCase
{
    use RefreshDatabase;

    public function test_forgot_password_page_is_available(): void
    {
        $this->get(route('password.request'))
            ->assertSee('Restablecer contraseña')
            ->assertSee(route('password.email'), false);
    }

    public function test_reset_link_is_sent_for_an_existing_account(): void
    {
        Notification::fake();
        $user = User::factory()->create(['email' => 'guest@overlook.test']);

        $response = $this->from(route('password.request'))->post(route('password.email'), [
            'email' => 'guest@overlook.test',
        ]);

        $response->assertRedirect(route('password.request'));
        $response->assertSessionHas('status');
        Notification::assertSentTo($user, ResetPasswordNotification::class);
    }

    public function test_unknown_email_does_not_reveal_whether_the_account_exists(): void
    {
        Notification::fake();

        $response = $this->from(route('password.request'))->post(route('password.email'), [
            'email' => 'missing@overlook.test',
        ]);

        $response->assertRedirect(route('password.request'));
        $response->assertSessionHas('status');
        Notification::assertNothingSent();
    }

    public function test_valid_token_resets_the_password_and_signs_the_user_in(): void
    {
        $user = User::factory()->create([
            'email' => 'guest@overlook.test',
            'password' => 'password',
        ]);
        $token = Password::broker()->createToken($user);

        $response = $this->post(route('password.update'), [
            'token' => $token,
            'email' => 'guest@overlook.test',
            'password' => 'new-password',
            'password_confirmation' => 'new-password',
        ]);

        $response->assertRedirect(route('guest.reservations.index'));
        $this->assertAuthenticatedAs($user->fresh());
        $this->assertTrue(Hash::check('new-password', $user->fresh()->password));
    }

    public function test_google_only_account_can_set_a_password_through_reset(): void
    {
        $user = User::factory()->create([
            'email' => 'oauth@gmail.com',
            'password' => null,
            'role' => UserRole::Guest,
        ]);
        $token = Password::broker()->createToken($user);

        $this->post(route('password.update'), [
            'token' => $token,
            'email' => 'oauth@gmail.com',
            'password' => 'new-password',
            'password_confirmation' => 'new-password',
        ])->assertRedirect();

        $this->assertTrue($user->fresh()->hasPassword());
        $this->assertTrue(Hash::check('new-password', $user->fresh()->password));
    }

    public function test_invalid_token_is_rejected(): void
    {
        $user = User::factory()->create(['email' => 'guest@overlook.test']);

        $response = $this->from(route('password.reset', 'bad-token'))
            ->post(route('password.update'), [
                'token' => 'bad-token',
                'email' => $user->email,
                'password' => 'new-password',
                'password_confirmation' => 'new-password',
            ]);

        $response->assertRedirect(route('password.reset', 'bad-token'));
        $response->assertSessionHasErrors('email');
        $this->assertTrue(Hash::check('password', $user->fresh()->password));
    }
}

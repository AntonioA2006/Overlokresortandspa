<?php

namespace Tests\Feature\Auth;

use App\Enums\AuthProvider;
use App\Enums\UserRole;
use App\Models\User;
use App\Notifications\VerifyEmailNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class EmailVerificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_sends_a_verification_email_and_leaves_the_account_unverified(): void
    {
        Notification::fake();

        $this->post(route('register.store'), [
            'name' => 'Ana Huésped',
            'email' => 'ana@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ])->assertRedirect(route('guest.reservations.index'));

        $user = User::query()->where('email', 'ana@example.com')->first();

        $this->assertNotNull($user);
        $this->assertNull($user->email_verified_at);
        Notification::assertSentTo($user, VerifyEmailNotification::class);
    }

    public function test_unverified_password_guest_is_redirected_from_my_stays_to_the_notice(): void
    {
        $user = User::factory()->unverified()->create([
            'email' => 'ana@example.com',
            'role' => UserRole::Guest,
        ]);

        $this->actingAs($user)
            ->get(route('guest.reservations.index'))
            ->assertRedirect(route('verification.notice'));

        $this->actingAs($user)
            ->get(route('verification.notice'))
            ->assertSee('Verifica tu correo')
            ->assertSee('ana@example.com', false);
    }

    public function test_google_account_with_verified_email_can_open_my_stays(): void
    {
        $user = User::factory()->create([
            'role' => UserRole::Guest,
            'auth_provider' => AuthProvider::Google,
            'auth_provider_id' => 'google-verified-user',
            'email_verified_at' => now(),
        ]);

        $this->actingAs($user)
            ->get(route('guest.reservations.index'))
            ->assertOk();
    }

    public function test_signed_verification_link_marks_the_email_as_verified(): void
    {
        $user = User::factory()->unverified()->create([
            'email' => 'ana@example.com',
            'role' => UserRole::Guest,
        ]);

        $url = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            [
                'id' => $user->id,
                'hash' => sha1($user->email),
            ],
        );

        $this->actingAs($user)
            ->get($url)
            ->assertRedirect(route('guest.reservations.index'));

        $this->assertNotNull($user->fresh()->email_verified_at);
    }

    public function test_invalid_verification_hash_is_rejected(): void
    {
        $user = User::factory()->unverified()->create(['role' => UserRole::Guest]);

        $url = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            [
                'id' => $user->id,
                'hash' => sha1('other@example.com'),
            ],
        );

        $this->actingAs($user)
            ->get($url)
            ->assertForbidden();

        $this->assertNull($user->fresh()->email_verified_at);
    }

    public function test_resend_sends_another_verification_email(): void
    {
        Notification::fake();

        $user = User::factory()->unverified()->create(['role' => UserRole::Guest]);

        $this->actingAs($user)
            ->from(route('verification.notice'))
            ->post(route('verification.send'))
            ->assertRedirect(route('verification.notice'));

        Notification::assertSentTo($user, VerifyEmailNotification::class);
    }

    public function test_changing_email_clears_verification_and_sends_a_new_link(): void
    {
        Notification::fake();

        $user = User::factory()->create([
            'email' => 'ana@example.com',
            'role' => UserRole::Guest,
        ]);

        $this->actingAs($user)->post(route('profile.update'), [
            'name' => $user->name,
            'email' => 'ana.nueva@example.com',
        ])->assertRedirect();

        $user->refresh();
        $this->assertSame('ana.nueva@example.com', $user->email);
        $this->assertNull($user->email_verified_at);
        Notification::assertSentTo($user, VerifyEmailNotification::class);
    }
}

<?php

namespace Tests\Unit\Notifications;

use App\Models\User;
use App\Notifications\ResetPasswordNotification;
use Tests\TestCase;

class ResetPasswordNotificationTest extends TestCase
{
    public function test_reset_mail_includes_hotel_branding_and_reset_link(): void
    {
        $user = User::factory()->make([
            'name' => 'Ana Huésped',
            'email' => 'ana@overlook.test',
        ]);

        $html = (new ResetPasswordNotification('reset-token-value'))->toMail($user)->render();

        $this->assertStringContainsString('Overlook Resort &amp; Spa', $html);
        $this->assertStringContainsString('Ana Huésped', $html);
        $this->assertStringContainsString('/reset-password/reset-token-value', $html);
        $this->assertStringContainsString('ana%40overlook.test', $html);
        $this->assertStringContainsString('Restablece tu contraseña', $html);
    }
}

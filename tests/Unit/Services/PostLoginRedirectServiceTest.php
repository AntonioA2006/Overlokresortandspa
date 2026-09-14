<?php

namespace Tests\Unit\Services;

use App\Enums\UserRole;
use App\Models\User;
use App\Services\Auth\PostLoginRedirectService;
use Tests\TestCase;

class PostLoginRedirectServiceTest extends TestCase
{
    public function test_it_redirects_each_role_to_the_expected_panel(): void
    {
        $service = app(PostLoginRedirectService::class);

        $this->assertSame(
            route('guest.reservations.index'),
            $service->redirectPath(new User(['role' => UserRole::Guest]))
        );

        $this->assertSame(
            route('reception.dashboard'),
            $service->redirectPath(new User(['role' => UserRole::Reception]))
        );

        $this->assertSame(
            route('support.dashboard'),
            $service->redirectPath(new User(['role' => UserRole::Support]))
        );

        $this->assertSame(
            route('admin.dashboard'),
            $service->redirectPath(new User(['role' => UserRole::Admin]))
        );
    }
}

<?php

namespace Tests\Feature\Admin;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_operational_dashboard(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin]);

        $response = $this->actingAs($admin)->get(route('admin.dashboard'));

        $response->assertSee('Panel administrativo');
        $response->assertSee('Reservaciones recientes');
        $response->assertDontSee('Próximamente');
    }

    public function test_reception_cannot_view_admin_dashboard(): void
    {
        $reception = User::factory()->create(['role' => UserRole::Reception]);

        $this->actingAs($reception)
            ->get(route('admin.dashboard'))
            ->assertForbidden();
    }
}

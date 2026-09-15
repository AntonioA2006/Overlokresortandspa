<?php

namespace Tests\Feature\Admin;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_reception_cannot_list_users(): void
    {
        $reception = User::factory()->create(['role' => UserRole::Reception]);

        $this->actingAs($reception)
            ->get(route('admin.users.index'))
            ->assertForbidden();
    }

    public function test_admin_can_create_a_staff_user(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin]);

        $this->actingAs($admin)->post(route('admin.users.store'), [
            'name' => 'Recepción Norte',
            'email' => 'north@overlook.test',
            'password' => 'password',
            'password_confirmation' => 'password',
            'role' => UserRole::Reception->value,
        ])->assertRedirect(route('admin.users.index'));

        $this->assertDatabaseHas('users', [
            'email' => 'north@overlook.test',
            'role' => UserRole::Reception->value,
        ]);
    }

    public function test_admin_can_change_a_user_role(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin]);
        $guest = User::factory()->create([
            'name' => 'Ana',
            'email' => 'ana@overlook.test',
            'role' => UserRole::Guest,
        ]);

        $this->actingAs($admin)->post(route('admin.users.update', $guest), [
            'name' => 'Ana',
            'email' => 'ana@overlook.test',
            'role' => UserRole::Support->value,
        ])->assertRedirect(route('admin.users.index'));

        $this->assertSame(UserRole::Support, $guest->fresh()->role);
    }

    public function test_last_admin_cannot_be_demoted(): void
    {
        $admin = User::factory()->create([
            'name' => 'Único Admin',
            'email' => 'admin@overlook.test',
            'role' => UserRole::Admin,
        ]);

        $this->actingAs($admin)->from(route('admin.users.edit', $admin))->post(route('admin.users.update', $admin), [
            'name' => 'Único Admin',
            'email' => 'admin@overlook.test',
            'role' => UserRole::Reception->value,
        ])->assertRedirect(route('admin.users.edit', $admin))
            ->assertSessionHasErrors('role');

        $this->assertSame(UserRole::Admin, $admin->fresh()->role);
    }

    public function test_admin_policy_refuses_guests(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin]);
        $guest = User::factory()->create(['role' => UserRole::Guest]);

        $this->assertTrue($admin->can('viewAny', User::class));
        $this->assertFalse($guest->can('update', $admin));
    }
}

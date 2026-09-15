<?php

namespace Tests\Feature\Admin;

use App\Enums\UserRole;
use App\Models\RoomType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoomTypeRatesTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_edit_rates(): void
    {
        $guest = User::factory()->create(['role' => UserRole::Guest]);
        $roomType = RoomType::factory()->create();

        $this->actingAs($guest)
            ->get(route('admin.room-types.edit', $roomType))
            ->assertForbidden();
    }

    public function test_admin_can_update_nightly_rate(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin]);
        $roomType = RoomType::factory()->create([
            'name' => 'Garden Suite',
            'base_price_per_night' => 2500,
            'max_guests' => 2,
            'is_active' => true,
        ]);

        $this->actingAs($admin)->post(route('admin.room-types.update', $roomType), [
            'name' => 'Garden Suite',
            'base_price_per_night' => 3200,
            'max_guests' => 3,
            'is_active' => '1',
            'description' => 'Actualizada',
        ])->assertRedirect(route('admin.room-types.index'));

        $roomType->refresh();
        $this->assertSame('3200.00', $roomType->base_price_per_night);
        $this->assertSame(3, $roomType->max_guests);
        $this->assertTrue($roomType->is_active);
    }

    public function test_admin_can_deactivate_a_room_type(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin]);
        $roomType = RoomType::factory()->create(['is_active' => true]);

        $this->actingAs($admin)->post(route('admin.room-types.update', $roomType), [
            'name' => $roomType->name,
            'base_price_per_night' => $roomType->base_price_per_night,
            'max_guests' => $roomType->max_guests,
        ])->assertRedirect(route('admin.room-types.index'));

        $this->assertFalse($roomType->fresh()->is_active);
    }

    public function test_admin_policy_allows_rate_updates(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin]);
        $guest = User::factory()->create(['role' => UserRole::Guest]);
        $roomType = RoomType::factory()->create();

        $this->assertTrue($admin->can('update', $roomType));
        $this->assertFalse($guest->can('update', $roomType));
    }
}

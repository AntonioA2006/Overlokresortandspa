<?php

namespace Tests\Feature\Admin;

use App\Enums\UserRole;
use App\Models\RoomType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
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
        $this->assertTrue($admin->can('create', RoomType::class));
        $this->assertFalse($guest->can('create', RoomType::class));
    }

    public function test_admin_can_create_a_room_type_with_a_cover_photo(): void
    {
        Storage::fake('public');

        $admin = User::factory()->create(['role' => UserRole::Admin]);
        $cover = UploadedFile::fake()->image('suite.jpg', 800, 600);

        $this->actingAs($admin)->post(route('admin.room-types.store'), [
            'name' => 'Cliff Suite',
            'base_price_per_night' => 4100,
            'max_guests' => 3,
            'is_active' => '1',
            'description' => 'Vista al acantilado',
            'cover' => $cover,
        ])->assertRedirect(route('admin.room-types.index'));

        $roomType = RoomType::query()->where('name', 'Cliff Suite')->first();

        $this->assertNotNull($roomType);
        $this->assertSame('cliff-suite', $roomType->slug);
        $this->assertSame('4100.00', $roomType->base_price_per_night);
        $this->assertTrue($roomType->is_active);
        $this->assertNotNull($roomType->cover_path);
        $this->assertStringStartsWith('storage/', $roomType->cover_path);
        Storage::disk('public')->assertExists(substr($roomType->cover_path, strlen('storage/')));
    }

    public function test_reception_cannot_create_room_types(): void
    {
        $reception = User::factory()->create(['role' => UserRole::Reception]);

        $this->actingAs($reception)->post(route('admin.room-types.store'), [
            'name' => 'Intrusa',
            'base_price_per_night' => 1000,
            'max_guests' => 2,
            'is_active' => '1',
        ])->assertForbidden();

        $this->assertDatabaseMissing('room_types', ['name' => 'Intrusa']);
    }
}

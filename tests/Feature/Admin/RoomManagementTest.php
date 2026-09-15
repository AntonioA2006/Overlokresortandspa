<?php

namespace Tests\Feature\Admin;

use App\Enums\RoomStatus;
use App\Enums\UserRole;
use App\Models\Room;
use App\Models\RoomType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoomManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_forbidden_from_room_admin(): void
    {
        $guest = User::factory()->create(['role' => UserRole::Guest]);

        $this->actingAs($guest)
            ->get(route('admin.rooms.index'))
            ->assertForbidden();
    }

    public function test_admin_can_create_a_room(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin]);
        $roomType = RoomType::factory()->create(['name' => 'Garden Suite']);

        $this->actingAs($admin)->post(route('admin.rooms.store'), [
            'room_type_id' => $roomType->id,
            'number' => '305',
            'floor' => 3,
            'status' => RoomStatus::Available->value,
            'description' => 'Vista al jardín',
        ])->assertRedirect(route('admin.rooms.index'));

        $this->assertDatabaseHas('rooms', [
            'number' => '305',
            'room_type_id' => $roomType->id,
            'status' => RoomStatus::Available->value,
        ]);
    }

    public function test_admin_can_toggle_room_status(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin]);
        $room = Room::factory()->create(['status' => RoomStatus::Available]);

        $this->actingAs($admin)->post(route('admin.rooms.status', $room), [
            'status' => RoomStatus::Maintenance->value,
        ])->assertRedirect();

        $this->assertSame(RoomStatus::Maintenance, $room->fresh()->status);
    }

    public function test_admin_can_update_a_room(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin]);
        $room = Room::factory()->create(['number' => '101', 'status' => RoomStatus::Available]);
        $otherType = RoomType::factory()->create();

        $this->actingAs($admin)->post(route('admin.rooms.update', $room), [
            'room_type_id' => $otherType->id,
            'number' => '101A',
            'floor' => 1,
            'status' => RoomStatus::Reserved->value,
            'description' => 'Actualizada',
        ])->assertRedirect(route('admin.rooms.index'));

        $room->refresh();
        $this->assertSame('101A', $room->number);
        $this->assertSame($otherType->id, $room->room_type_id);
        $this->assertSame(RoomStatus::Reserved, $room->status);
    }

    public function test_reception_cannot_create_rooms(): void
    {
        $reception = User::factory()->create(['role' => UserRole::Reception]);
        $roomType = RoomType::factory()->create();

        $this->actingAs($reception)->post(route('admin.rooms.store'), [
            'room_type_id' => $roomType->id,
            'number' => '999',
            'status' => RoomStatus::Available->value,
        ])->assertForbidden();
    }

    public function test_admin_policy_allows_room_updates(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin]);
        $guest = User::factory()->create(['role' => UserRole::Guest]);
        $room = Room::factory()->create();

        $this->assertTrue($admin->can('update', $room));
        $this->assertFalse($guest->can('update', $room));
        $this->assertFalse($guest->can('create', Room::class));
    }
}

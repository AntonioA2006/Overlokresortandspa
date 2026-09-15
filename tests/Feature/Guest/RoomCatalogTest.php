<?php

namespace Tests\Feature\Guest;

use App\Models\RoomType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoomCatalogTest extends TestCase
{
    use RefreshDatabase;

    public function test_catalog_lists_active_room_types(): void
    {
        $roomType = RoomType::factory()->create([
            'name' => 'Suite Ocean View',
            'slug' => 'suite-ocean-view',
            'is_active' => true,
        ]);
        RoomType::factory()->create([
            'name' => 'Hidden Loft',
            'slug' => 'hidden-loft',
            'is_active' => false,
        ]);

        $response = $this->get(route('guest.rooms.index'));

        $response->assertSee('Suite Ocean View');
        $response->assertDontSee('Hidden Loft');
        $response->assertSee(route('guest.reservations.rooms.show', $roomType), false);
        $response->assertDontSee('Catálogo de habitaciones');
    }
}

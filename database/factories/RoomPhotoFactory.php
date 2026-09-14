<?php

namespace Database\Factories;

use App\Models\Room;
use App\Models\RoomPhoto;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<RoomPhoto>
 */
class RoomPhotoFactory extends Factory
{
    protected $model = RoomPhoto::class;

    public function definition(): array
    {
        return [
            'room_id' => Room::factory(),
            'path' => 'images/landing/room.jpg',
            'alt_text' => fake()->sentence(3),
            'sort_order' => 1,
            'is_primary' => true,
        ];
    }
}

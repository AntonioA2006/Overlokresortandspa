<?php

namespace Database\Factories;

use App\Enums\RoomStatus;
use App\Models\Room;
use App\Models\RoomType;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Room>
 */
class RoomFactory extends Factory
{
    protected $model = Room::class;

    public function definition(): array
    {
        return [
            'room_type_id' => RoomType::factory(),
            'number' => fake()->unique()->numerify('1##'),
            'floor' => 1,
            'status' => RoomStatus::Available,
            'description' => fake()->sentence(),
        ];
    }

    public function maintenance(): static
    {
        return $this->state(fn () => ['status' => RoomStatus::Maintenance]);
    }
}

<?php

namespace Database\Factories;

use App\Enums\ReservationStatus;
use App\Models\Reservation;
use App\Models\Room;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Reservation>
 */
class ReservationFactory extends Factory
{
    protected $model = Reservation::class;

    public function definition(): array
    {
        $checkIn = now()->addDays(5)->toDateString();
        $checkOut = now()->addDays(8)->toDateString();

        return [
            'code' => 'OVL-'.Str::upper(Str::random(8)),
            'user_id' => User::factory(),
            'room_id' => Room::factory(),
            'check_in_date' => $checkIn,
            'check_out_date' => $checkOut,
            'guests_count' => 2,
            'price_total' => 7500,
            'status' => ReservationStatus::Confirmed,
        ];
    }
}

<?php

namespace App\Services;

use App\Models\Room;
use Carbon\CarbonInterface;

class PricingService
{
    public function calculateTotal(Room $room, CarbonInterface $checkIn, CarbonInterface $checkOut): array
    {
        $nights = max($checkIn->diffInDays($checkOut), 1);
        $pricePerNight = (float) $room->roomType->base_price_per_night;
        $subtotal = round($pricePerNight * $nights, 2);

        return [
            'nights' => $nights,
            'price_per_night' => $pricePerNight,
            'subtotal' => $subtotal,
            'taxes' => 0.0,
            'discounts' => 0.0,
            'total' => $subtotal,
            'currency' => config('overlook.currency', 'MXN'),
        ];
    }
}

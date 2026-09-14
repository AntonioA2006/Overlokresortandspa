<?php

namespace App\Support;

use App\Models\Room;
use App\Models\RoomPhoto;
use App\Models\RoomType;
use Illuminate\Support\Collection;

class RoomTypePresentation
{
    /**
     * @param  Collection<int, Room>  $rooms
     * @return array{url: string, alt: string}|null
     */
    public static function resolvePhoto(Collection $rooms, RoomType $roomType): ?array
    {
        foreach ($rooms as $room) {
            /** @var RoomPhoto|null $photo */
            $photo = $room->photos->firstWhere('is_primary', true) ?? $room->photos->first();

            if ($photo !== null) {
                return [
                    'url' => asset($photo->path),
                    'alt' => $photo->alt_text ?: __('reservations.photo_alt', ['name' => $roomType->name]),
                ];
            }
        }

        return null;
    }

    public static function fallbackPhoto(RoomType $roomType): array
    {
        return [
            'url' => asset('images/landing/room.jpg'),
            'alt' => __('reservations.photo_alt', ['name' => $roomType->name]),
        ];
    }
}

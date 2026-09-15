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
        if (filled($roomType->cover_path)) {
            return [
                'url' => asset($roomType->cover_path),
                'alt' => __('reservations.photo_alt', ['name' => $roomType->name]),
            ];
        }

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

    /**
     * @param  Collection<int, Room>  $rooms
     * @return list<array{url: string, alt: string}>
     */
    public static function resolveGallery(Collection $rooms, RoomType $roomType): array
    {
        $photos = [];

        if (filled($roomType->cover_path)) {
            $photos[] = [
                'url' => asset($roomType->cover_path),
                'alt' => __('reservations.photo_alt', ['name' => $roomType->name]),
            ];
        }

        foreach ($rooms as $room) {
            foreach ($room->photos as $photo) {
                $photos[] = [
                    'url' => asset($photo->path),
                    'alt' => $photo->alt_text ?: __('reservations.photo_alt', ['name' => $roomType->name]),
                ];
            }
        }

        if ($photos === []) {
            return [self::fallbackPhoto($roomType)];
        }

        $unique = [];
        $seenUrls = [];

        foreach ($photos as $photo) {
            if (in_array($photo['url'], $seenUrls, true)) {
                continue;
            }

            $seenUrls[] = $photo['url'];
            $unique[] = $photo;
        }

        return $unique;
    }

    public static function fallbackPhoto(RoomType $roomType): array
    {
        return [
            'url' => asset('images/landing/room.jpg'),
            'alt' => __('reservations.photo_alt', ['name' => $roomType->name]),
        ];
    }
}

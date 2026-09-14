<?php

namespace App\Services;

use App\Enums\ReservationStatus;
use App\Enums\RoomStatus;
use App\Models\Room;
use App\Models\RoomType;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class AvailabilityService
{
    /**
     * @return Collection<int, Room>
     */
    public function availableRooms(
        CarbonInterface $checkIn,
        CarbonInterface $checkOut,
        int $guests,
    ): Collection {
        return Room::query()
            ->with(['roomType.amenities', 'photos'])
            ->where('status', RoomStatus::Available)
            ->whereHas('roomType', fn (Builder $query) => $query
                ->where('is_active', true)
                ->where('max_guests', '>=', $guests))
            ->whereDoesntHave('reservations', fn (Builder $query) => $query
                ->whereNotIn('status', [
                    ReservationStatus::Cancelled->value,
                    ReservationStatus::CheckedOut->value,
                    ReservationStatus::NoShow->value,
                ])
                ->where('check_in_date', '<', $checkOut->toDateString())
                ->where('check_out_date', '>', $checkIn->toDateString()))
            ->orderBy('number')
            ->get();
    }

    public function isRoomAvailable(
        Room $room,
        CarbonInterface $checkIn,
        CarbonInterface $checkOut,
        int $guests,
    ): bool {
        if ($room->status !== RoomStatus::Available) {
            return false;
        }

        if (! $room->roomType?->is_active || $room->roomType->max_guests < $guests) {
            return false;
        }

        return ! $room->reservations()
            ->whereNotIn('status', [
                ReservationStatus::Cancelled->value,
                ReservationStatus::CheckedOut->value,
                ReservationStatus::NoShow->value,
            ])
            ->where('check_in_date', '<', $checkOut->toDateString())
            ->where('check_out_date', '>', $checkIn->toDateString())
            ->exists();
    }

    /**
     * @return Collection<int, Room>
     */
    public function availableRoomsForType(
        RoomType $roomType,
        CarbonInterface $checkIn,
        CarbonInterface $checkOut,
        int $guests,
    ): Collection {
        if (! $roomType->is_active || $roomType->max_guests < $guests) {
            return collect();
        }

        return $this->availableRooms($checkIn, $checkOut, $guests)
            ->where('room_type_id', $roomType->id)
            ->values();
    }

    /**
     * @return Collection<int, array{room_type: RoomType, available_count: int, rooms: Collection<int, Room>}>
     */
    public function availableRoomsGroupedByType(
        CarbonInterface $checkIn,
        CarbonInterface $checkOut,
        int $guests,
    ): Collection {
        return $this->availableRooms($checkIn, $checkOut, $guests)
            ->groupBy('room_type_id')
            ->map(function (Collection $rooms): array {
                /** @var Room $firstRoom */
                $firstRoom = $rooms->first();

                return [
                    'room_type' => $firstRoom->roomType,
                    'available_count' => $rooms->count(),
                    'rooms' => $rooms->values(),
                ];
            })
            ->sortBy(fn (array $group) => $group['room_type']->base_price_per_night)
            ->values();
    }
}

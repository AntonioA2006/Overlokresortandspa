<?php

namespace App\Enums;

enum RoomStatus: string
{
    case Available = 'available';
    case Occupied = 'occupied';
    case Maintenance = 'maintenance';
    case Reserved = 'reserved';

    public function label(): string
    {
        return match ($this) {
            self::Available => __('enums.room_status.available'),
            self::Occupied => __('enums.room_status.occupied'),
            self::Maintenance => __('enums.room_status.maintenance'),
            self::Reserved => __('enums.room_status.reserved'),
        };
    }

    /**
     * @return list<string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}

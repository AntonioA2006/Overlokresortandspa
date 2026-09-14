<?php

namespace App\Enums;

enum NotificationType: string
{
    case ReservationConfirmed = 'reservation_confirmed';
    case ReservationCancelled = 'reservation_cancelled';
    case ArrivalReminder = 'arrival_reminder';
    case RoomDeliveryReminder = 'room_delivery_reminder';
    case RoomAvailable = 'room_available';
    case RoomDelivered = 'room_delivered';
    case SupportMessage = 'support_message';
    case HotelInfo = 'hotel_info';

    public function label(): string
    {
        return match ($this) {
            self::ReservationConfirmed => 'Reservación confirmada',
            self::ReservationCancelled => 'Reservación cancelada',
            self::ArrivalReminder => 'Recordatorio de llegada',
            self::RoomDeliveryReminder => 'Recordatorio de entrega',
            self::RoomAvailable => 'Habitación disponible',
            self::RoomDelivered => 'Habitación entregada',
            self::SupportMessage => 'Mensaje de soporte',
            self::HotelInfo => 'Información del hotel',
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

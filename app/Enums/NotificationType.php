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
            self::ReservationConfirmed => __('enums.notification_type.reservation_confirmed'),
            self::ReservationCancelled => __('enums.notification_type.reservation_cancelled'),
            self::ArrivalReminder => __('enums.notification_type.arrival_reminder'),
            self::RoomDeliveryReminder => __('enums.notification_type.room_delivery_reminder'),
            self::RoomAvailable => __('enums.notification_type.room_available'),
            self::RoomDelivered => __('enums.notification_type.room_delivered'),
            self::SupportMessage => __('enums.notification_type.support_message'),
            self::HotelInfo => __('enums.notification_type.hotel_info'),
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

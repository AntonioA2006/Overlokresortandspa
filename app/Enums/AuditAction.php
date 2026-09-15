<?php

namespace App\Enums;

enum AuditAction: string
{
    case ReservationCreated = 'reservation_created';
    case ReservationConfirmed = 'reservation_confirmed';
    case ReservationCancelled = 'reservation_cancelled';
    case QrScanned = 'qr_scanned';
    case IdentityVerified = 'identity_verified';
    case CheckInCompleted = 'check_in_completed';
    case RoomDelivered = 'room_delivered';
    case CheckOutCompleted = 'check_out_completed';
    case AdminChange = 'admin_change';

    public function label(): string
    {
        return match ($this) {
            self::ReservationCreated => __('enums.audit.reservation_created'),
            self::ReservationConfirmed => __('enums.audit.reservation_confirmed'),
            self::ReservationCancelled => __('enums.audit.reservation_cancelled'),
            self::QrScanned => __('enums.audit.qr_scanned'),
            self::IdentityVerified => __('enums.audit.identity_verified'),
            self::CheckInCompleted => __('enums.audit.check_in_completed'),
            self::RoomDelivered => __('enums.audit.room_delivered'),
            self::CheckOutCompleted => __('enums.audit.check_out_completed'),
            self::AdminChange => __('enums.audit.admin_change'),
        };
    }
}

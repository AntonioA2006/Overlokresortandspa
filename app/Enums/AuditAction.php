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
            self::ReservationCreated => 'Reservación creada',
            self::ReservationConfirmed => 'Reservación confirmada',
            self::ReservationCancelled => 'Reservación cancelada',
            self::QrScanned => 'QR escaneado',
            self::IdentityVerified => 'Identidad verificada',
            self::CheckInCompleted => 'Check-in completado',
            self::RoomDelivered => 'Habitación entregada',
            self::CheckOutCompleted => 'Check-out completado',
            self::AdminChange => 'Cambio administrativo',
        };
    }
}

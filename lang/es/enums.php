<?php

return [

    'roles' => [
        'guest' => 'Huésped',
        'reception' => 'Recepción',
        'support' => 'Soporte',
        'admin' => 'Administrador',
    ],

    'room_status' => [
        'available' => 'Disponible',
        'occupied' => 'Ocupada',
        'maintenance' => 'Mantenimiento',
        'reserved' => 'Reservada',
    ],

    'reservation_status' => [
        'pending' => 'Pendiente',
        'confirmed' => 'Confirmada',
        'cancelled' => 'Cancelada',
        'checked_in' => 'Check-in realizado',
        'checked_out' => 'Check-out realizado',
        'no_show' => 'No se presentó',
    ],

    'audit' => [
        'reservation_created' => 'Reservación creada',
        'reservation_confirmed' => 'Reservación confirmada',
        'reservation_cancelled' => 'Reservación cancelada',
        'qr_scanned' => 'QR escaneado',
        'identity_verified' => 'Identidad verificada',
        'check_in_completed' => 'Check-in completado',
        'room_delivered' => 'Habitación entregada',
        'check_out_completed' => 'Check-out completado',
        'admin_change' => 'Cambio administrativo',
    ],

    'notification_type' => [
        'reservation_confirmed' => 'Reservación confirmada',
        'reservation_cancelled' => 'Reservación cancelada',
        'arrival_reminder' => 'Recordatorio de llegada',
        'room_delivery_reminder' => 'Recordatorio de entrega',
        'room_available' => 'Habitación disponible',
        'room_delivered' => 'Habitación entregada',
        'support_message' => 'Mensaje de soporte',
        'hotel_info' => 'Información del hotel',
    ],

];

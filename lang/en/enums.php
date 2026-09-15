<?php

return [

    'roles' => [
        'guest' => 'Guest',
        'reception' => 'Reception',
        'support' => 'Support',
        'admin' => 'Administrator',
    ],

    'room_status' => [
        'available' => 'Available',
        'occupied' => 'Occupied',
        'maintenance' => 'Maintenance',
        'reserved' => 'Reserved',
    ],

    'reservation_status' => [
        'pending' => 'Pending',
        'confirmed' => 'Confirmed',
        'cancelled' => 'Cancelled',
        'checked_in' => 'Checked in',
        'checked_out' => 'Checked out',
        'no_show' => 'No-show',
    ],

    'audit' => [
        'reservation_created' => 'Reservation created',
        'reservation_confirmed' => 'Reservation confirmed',
        'reservation_cancelled' => 'Reservation cancelled',
        'qr_scanned' => 'QR scanned',
        'identity_verified' => 'Identity verified',
        'check_in_completed' => 'Check-in completed',
        'room_delivered' => 'Room delivered',
        'check_out_completed' => 'Check-out completed',
        'admin_change' => 'Administrative change',
    ],

    'notification_type' => [
        'reservation_confirmed' => 'Reservation confirmed',
        'reservation_cancelled' => 'Reservation cancelled',
        'arrival_reminder' => 'Arrival reminder',
        'room_delivery_reminder' => 'Room delivery reminder',
        'room_available' => 'Room available',
        'room_delivered' => 'Room delivered',
        'support_message' => 'Support message',
        'hotel_info' => 'Hotel information',
    ],

];

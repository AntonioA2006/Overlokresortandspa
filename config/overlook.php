<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Hotel identity
    |--------------------------------------------------------------------------
    */
    'hotel_name' => env('OVERLOOK_HOTEL_NAME', 'Overlook Resort & Spa'),
    'currency' => env('OVERLOOK_CURRENCY', 'MXN'),
    'timezone' => env('OVERLOOK_TIMEZONE', 'America/Mazatlan'),

    /*
    |--------------------------------------------------------------------------
    | Check-in / room delivery
    |--------------------------------------------------------------------------
    */
    'default_check_in_time' => env('OVERLOOK_CHECK_IN_TIME', '15:00'),
    'default_check_out_time' => env('OVERLOOK_CHECK_OUT_TIME', '11:00'),

    /*
    |--------------------------------------------------------------------------
    | Scheduled notification intervals (hours before check-in time)
    |--------------------------------------------------------------------------
    */
    'notification_intervals' => array_map(
        'intval',
        explode(',', env('OVERLOOK_NOTIFICATION_INTERVALS', '8,4,2,1'))
    ),

    /*
    |--------------------------------------------------------------------------
    | Reservation security tokens (QR)
    |--------------------------------------------------------------------------
    */
    'reservation_token_length' => (int) env('OVERLOOK_RESERVATION_TOKEN_LENGTH', 64),
    'reservation_code_prefix' => env('OVERLOOK_RESERVATION_CODE_PREFIX', 'OVL'),

    /*
    |--------------------------------------------------------------------------
    | Guest search limits
    |--------------------------------------------------------------------------
    */
    'max_guests_per_search' => (int) env('OVERLOOK_MAX_GUESTS_PER_SEARCH', 8),

];

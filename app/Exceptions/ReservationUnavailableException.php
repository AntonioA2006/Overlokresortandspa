<?php

namespace App\Exceptions;

use RuntimeException;

class ReservationUnavailableException extends RuntimeException
{
    public function __construct()
    {
        parent::__construct(__('reservations.errors.unavailable'));
    }
}

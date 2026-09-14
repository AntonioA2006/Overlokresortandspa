<?php

namespace App\Exceptions;

use RuntimeException;

class ReservationCancellationException extends RuntimeException
{
    public function __construct()
    {
        parent::__construct(__('reservations.errors.cancel_not_allowed'));
    }
}

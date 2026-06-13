<?php

namespace App\Exceptions\Booking;

use Exception;

class ScheduleNotAvailableException extends Exception
{
    public function __construct(string $message = 'The selected schedule is not available.')
    {
        parent::__construct($message);
    }
}

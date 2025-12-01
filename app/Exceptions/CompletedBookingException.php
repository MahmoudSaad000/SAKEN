<?php

namespace App\Exceptions;

use Exception;
use Throwable;

class CompletedBookingException extends Exception
{
    public function __construct(string $message = "You Can't Cancel Completed Booking.")
    {
        return parent::__construct($message);
    }
}

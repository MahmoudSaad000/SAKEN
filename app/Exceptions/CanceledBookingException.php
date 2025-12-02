<?php

namespace App\Exceptions;

use Exception;
use Throwable;

final class CanceledBookingException extends Exception
{
    public function __construct(string $message = "This Booking is Already Canceled.", int $code = 0, Throwable|null $previous = null)
    {
        return parent::__construct($message, $code, $previous);
    }
}

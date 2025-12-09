<?php

namespace App\Exceptions;

use Exception;

class DateConflictException extends Exception
{
    public function __construct($message = 'The selected date conflicts with an existing booking.', $code = 422)
    {
        parent::__construct($message, $code);
    }
}

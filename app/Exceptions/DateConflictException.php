<?php

namespace App\Exceptions;

use Exception;

class DateConflictException extends Exception
{
    public function __construct($message = "The selected date conflicts with an existing booking.")
    {
        parent::__construct($message);
    }
}

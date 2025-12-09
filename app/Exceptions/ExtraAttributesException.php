<?php

namespace App\Exceptions;

use Exception;

class ExtraAttributesException extends Exception
{
    protected $extraAttributes;

    public function __construct($extraAttributes, $message = 'Extra Attributes : ', $code = 422)
    {
        parent::__construct($message, $code);
        $this->extraAttributes = $extraAttributes;
    }

    public function getAttributes()
    {
        return $this->extraAttributes->implode(', ');
    }
}

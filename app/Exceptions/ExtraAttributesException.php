<?php

namespace App\Exceptions;

use Exception;

class ExtraAttributesException extends Exception
{
    protected $extraAttributes;
    public function __construct($extraAttributes,$message = "Extra Attributes : ")
    {
        parent::__construct($message);
        $this->extraAttributes = $extraAttributes;
    }
    public function getAttributes(){
        return $this->extraAttributes->implode(', ');
    }
}
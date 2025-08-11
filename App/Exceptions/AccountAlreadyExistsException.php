<?php

namespace App\Exceptions;

use InvalidArgumentException;
use Throwable;

class AccountAlreadyExistsException extends InvalidArgumentException
{
    public function __construct($message = "The specified account already exists.", $code = 0, ?Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}

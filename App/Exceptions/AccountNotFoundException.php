<?php

namespace App\Exceptions;

use InvalidArgumentException;
use Throwable;

class AccountNotFoundException extends InvalidArgumentException
{
    public function __construct($message = "The specified account was not found.", $code = 0, ?Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}

<?php

namespace App\Exceptions;

use InvalidArgumentException;
use Throwable;

class InvalidAmountException extends InvalidArgumentException
{
    public function __construct($message = "The provided amount is invalid.", $code = 0, ?Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}

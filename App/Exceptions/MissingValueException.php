<?php

namespace App\Exceptions;

use InvalidArgumentException;
use Throwable;

class MissingValueException extends InvalidArgumentException
{
    public function __construct($message = "Missing destination account ID.", $code = 0, ?Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}

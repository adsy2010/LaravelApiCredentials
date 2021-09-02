<?php

namespace Adsy2010\LaravelApiCredentials\Exceptions;

use Exception;
use Throwable;

class CredentialScopeAccessOutOfRangeException extends Exception
{
    public function __construct($message = "The scope access level requested is not available", $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}

<?php

namespace Adsy2010\LaravelApiCredentials\Exceptions;

use Exception;
use Throwable;

class CredentialUnavailableException extends Exception
{
    public function __construct($message = "No credentials contain the specified scopes and access", $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}

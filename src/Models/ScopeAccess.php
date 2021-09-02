<?php


namespace Adsy2010\LaravelApiCredentials\Models;


abstract class ScopeAccess
{
    const READ = 1;
    const WRITE = 2;
    const READ_AND_WRITE = 3;

    const ALL = [self::READ, self::WRITE, self::READ_AND_WRITE];
}

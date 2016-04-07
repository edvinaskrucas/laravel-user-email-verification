<?php

namespace Krucas\LaravelUserEmailVerification\Facades;

use Illuminate\Support\Facades\Facade;

class Verification extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'auth.verification';
    }
}
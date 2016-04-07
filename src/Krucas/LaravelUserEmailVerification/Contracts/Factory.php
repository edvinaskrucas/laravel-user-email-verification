<?php

namespace Krucas\LaravelUserEmailVerification\Contracts;

interface Factory
{
    /**
     * Get a verification broker instance by name.
     *
     * @param string|null $name
     * @return mixed
     */
    public function broker($name = null);
}
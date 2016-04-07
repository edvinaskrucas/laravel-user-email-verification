<?php

namespace Krucas\LaravelUserEmailVerification\Contracts;

interface RequiresEmailVerification
{
    /**
     * Get the e-mail address where verification links are sent.
     *
     * @return string
     */
    public function getEmailForVerification();
}

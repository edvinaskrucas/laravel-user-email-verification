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

    /**
     * Determine if user is verified or not.
     *
     * @return bool
     */
    public function isUserEmailVerified();
}

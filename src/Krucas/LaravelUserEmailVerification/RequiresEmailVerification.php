<?php

namespace Krucas\LaravelUserEmailVerification;

trait RequiresEmailVerification
{
    /**
     * Get the e-mail address where verification links are sent.
     *
     * @return string
     */
    public function getEmailForVerification()
    {
        return $this->email;
    }

    /**
     * Determine if user is verified or not.
     *
     * @return bool
     */
    public function isUserEmailVerified()
    {
        return (bool) $this->verified;
    }
}

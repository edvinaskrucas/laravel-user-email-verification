<?php

namespace Krucas\LaravelUserEmailVerification;

trait RedirectsUsers
{
    /**
     * Get the post register redirect path if user must verify account.
     *
     * @return string
     */
    public function verificationRedirectPath()
    {
        if (property_exists($this, 'verificationRedirectPath')) {
            return $this->verificationRedirectPath;
        }

        return property_exists($this, 'verificationRedirectTo')
            ? $this->verificationRedirectTo : route('verification.verify', [], false);
    }
}

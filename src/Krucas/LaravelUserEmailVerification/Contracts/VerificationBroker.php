<?php

namespace Krucas\LaravelUserEmailVerification\Contracts;

use Closure;

interface VerificationBroker
{
    /**
     * Constant representing a successfully sent verification link.
     *
     * @var string
     */
    const VERIFICATION_LINK_SENT = 'verification::verification.sent';

    /**
     * Constant representing a successfully verified account.
     *
     * @var string
     */
    const VERIFIED = 'verification::verification.verified';

    /**
     * Constant representing the user not found response.
     *
     * @var string
     */
    const INVALID_USER = 'verification::verification.user';

    /**
     * Constant representing an invalid token.
     *
     * @var string
     */
    const INVALID_TOKEN = 'verification::verification.token';

    /**
     * Send a user verification link.
     *
     * @param array $credentials
     * @param \Closure|null $callback
     * @return string
     */
    public function sendVerificationLink(array $credentials, Closure $callback = null);

    /**
     * Verify given account.
     *
     * @param array $credentials
     * @param \Closure $callback
     * @return mixed
     */
    public function verify(array $credentials, Closure $callback);
}

<?php

namespace Krucas\LaravelUserEmailVerification\Contracts;

interface TokenRepositoryInterface
{
    /**
     * Create a new token.
     *
     * @param \Krucas\LaravelUserEmailVerification\Contracts\RequiresEmailVerification $user
     * @return string
     */
    public function create(RequiresEmailVerification $user);

    /**
     * Determine if a token record exists and is valid.
     *
     * @param \Krucas\LaravelUserEmailVerification\Contracts\RequiresEmailVerification $user
     * @param string $token
     * @return bool
     */
    public function exists(RequiresEmailVerification $user, $token);

    /**
     * Delete token record.
     *
     * @param string $token
     * @return void
     */
    public function delete($token);

    /**
     * Delete expired tokens.
     *
     * @return void
     */
    public function deleteExpired();
}

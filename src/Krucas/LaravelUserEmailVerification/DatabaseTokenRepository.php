<?php

namespace Krucas\LaravelUserEmailVerification;

use Carbon\Carbon;
use Illuminate\Database\ConnectionInterface;
use Illuminate\Support\Str;
use Krucas\LaravelUserEmailVerification\Contracts;

class DatabaseTokenRepository implements Contracts\TokenRepositoryInterface
{
    /**
     * The database connection instance.
     *
     * @var \Illuminate\Database\ConnectionInterface
     */
    protected $connection;

    /**
     * The token database table.
     *
     * @var string
     */
    protected $table;

    /**
     * The hashing key.
     *
     * @var string
     */
    protected $hashKey;

    /**
     * The number of seconds a token should last.
     *
     * @var int
     */
    protected $expires;

    /**
     * Create a new token repository instance.
     *
     * @param \Illuminate\Database\ConnectionInterface $connection
     * @param string $table
     * @param string $hashKey
     * @param int $expires
     */
    public function __construct(ConnectionInterface $connection, $table, $hashKey, $expires = 0)
    {
        $this->table = $table;
        $this->hashKey = $hashKey;
        $this->expires = $expires;
        $this->connection = $connection;
    }

    /**
     * Create a new token.
     *
     * @param \Krucas\LaravelUserEmailVerification\Contracts\RequiresEmailVerification $user
     * @return string
     */
    public function create(Contracts\RequiresEmailVerification $user)
    {
        $email = $user->getEmailForVerification();

        $this->deleteExisting($user);

        $token = $this->createNewToken();

        $this->getTable()->insert($this->getPayload($email, $token));

        return $token;
    }

    /**
     * Delete all existing reset tokens from the database.
     *
     * @param \Krucas\LaravelUserEmailVerification\Contracts\RequiresEmailVerification $user
     * @return int
     */
    protected function deleteExisting(Contracts\RequiresEmailVerification $user)
    {
        return $this->getTable()->where('email', $user->getEmailForVerification())->delete();
    }

    /**
     * Build the record payload for the table.
     *
     * @param  string  $email
     * @param  string  $token
     * @return array
     */
    protected function getPayload($email, $token)
    {
        return ['email' => $email, 'token' => $token, 'created_at' => new Carbon];
    }

    /**
     * Determine if a token record exists and is valid.
     *
     * @param \Krucas\LaravelUserEmailVerification\Contracts\RequiresEmailVerification $user
     * @param string $token
     * @return bool
     */
    public function exists(Contracts\RequiresEmailVerification $user, $token)
    {
        $email = $user->getEmailForVerification();

        $token = (array) $this->getTable()->where('email', $email)->where('token', $token)->first();

        return $token && !$this->tokenExpired($token);
    }

    /**
     * Determine if the token has expired.
     *
     * @param  array  $token
     * @return bool
     */
    protected function tokenExpired($token)
    {
        if (!$this->isExpirationEnabled()) {
            return false;
        }

        $expirationTime = strtotime($token['created_at']) + $this->expires;

        return $expirationTime < $this->getCurrentTime();
    }

    /**
     * Get the current UNIX timestamp.
     *
     * @return int
     */
    protected function getCurrentTime()
    {
        return time();
    }

    /**
     * Delete token record.
     *
     * @param string $token
     * @return void
     */
    public function delete($token)
    {
        $this->getTable()->where('token', $token)->delete();
    }

    /**
     * Delete expired tokens.
     *
     * @return void
     */
    public function deleteExpired()
    {
        if ($this->isExpirationEnabled()) {
            $expiredAt = Carbon::now()->subSeconds($this->expires);

            $this->getTable()->where('created_at', '<', $expiredAt)->delete();
        }
    }

    /**
     * Create a new token for the user.
     *
     * @return string
     */
    public function createNewToken()
    {
        return hash_hmac('sha256', Str::random(40), $this->hashKey);
    }

    /**
     * Begin a new database query against the table.
     *
     * @return \Illuminate\Database\Query\Builder
     */
    protected function getTable()
    {
        return $this->connection->table($this->table);
    }

    /**
     * Determine if token expiration is enabled or disabled.
     *
     * @return bool
     */
    protected function isExpirationEnabled()
    {
        return $this->expires > 0;
    }

    /**
     * Get the database connection instance.
     *
     * @return \Illuminate\Database\ConnectionInterface
     */
    public function getConnection()
    {
        return $this->connection;
    }
}

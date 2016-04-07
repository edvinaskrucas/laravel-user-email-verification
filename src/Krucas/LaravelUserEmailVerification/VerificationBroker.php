<?php

namespace Krucas\LaravelUserEmailVerification;

use Closure;
use Illuminate\Support\Arr;
use UnexpectedValueException;
use Illuminate\Contracts\Auth\UserProvider;
use Illuminate\Contracts\Mail\Mailer;
use Krucas\LaravelUserEmailVerification\Contracts;

class VerificationBroker implements Contracts\VerificationBroker
{
    /**
     * The verification token repository.
     *
     * @var \Krucas\LaravelUserEmailVerification\Contracts\TokenRepositoryInterface
     */
    protected $tokens;

    /**
     * The user provider implementation.
     *
     * @var \Illuminate\Contracts\Auth\UserProvider
     */
    protected $users;

    /**
     * The mailer instance.
     *
     * @var \Illuminate\Contracts\Mail\Mailer
     */
    protected $mailer;

    /**
     * The view of the activation link e-mail.
     *
     * @var string
     */
    protected $emailView;

    /**
     * @param \Krucas\LaravelUserEmailVerification\Contracts\TokenRepositoryInterface $tokens
     * @param \Illuminate\Contracts\Auth\UserProvider $users
     * @param \Illuminate\Contracts\Mail\Mailer $mailer
     * @param string $emailView
     */
    public function __construct(Contracts\TokenRepositoryInterface $tokens, UserProvider $users, Mailer $mailer, $emailView)
    {
        $this->tokens = $tokens;
        $this->users = $users;
        $this->mailer = $mailer;
        $this->emailView = $emailView;
    }

    /**
     * Send a user verification link.
     *
     * @param array $credentials
     * @param \Closure|null $callback
     * @return string
     */
    public function sendVerificationLink(array $credentials, Closure $callback = null)
    {
        $user = $this->getUser($credentials);

        if (is_null($user)) {
            return Contracts\VerificationBroker::INVALID_USER;
        }

        $token = $this->tokens->create($user);

        $this->emailVerificationLink($user, $token, $callback);

        return Contracts\VerificationBroker::VERIFICATION_LINK_SENT;
    }

    /**
     * Send the email verification link via e-mail.
     *
     * @param \Krucas\LaravelUserEmailVerification\Contracts\RequiresEmailVerification $user
     * @param string $token
     * @param \Closure|null $callback
     * @return int
     */
    public function emailVerificationLink(Contracts\RequiresEmailVerification $user, $token, Closure $callback = null)
    {
        $view = $this->emailView;

        return $this->mailer->send($view, compact('token', 'user'), function ($message) use ($user, $token, $callback) {
            $message->to($user->getEmailForVerification());

            if (!is_null($callback)) {
                call_user_func($callback, $message, $user, $token);
            }
        });
    }

    /**
     * Verify given account.
     *
     * @param array $credentials
     * @param \Closure $callback
     * @return mixed
     */
    public function verify(array $credentials, Closure $callback)
    {
        $user = $this->validateVerification($credentials);

        if (!$user instanceof Contracts\RequiresEmailVerification) {
            return $user;
        }

        call_user_func($callback, $user);

        $this->tokens->delete($credentials['token']);

        return Contracts\VerificationBroker::VERIFIED;
    }

    /**
     * Validate verification for the given credentials.
     *
     * @param array $credentials
     * @return \Krucas\LaravelUserEmailVerification\Contracts\RequiresEmailVerification
     */
    protected function validateVerification(array $credentials)
    {
        if (is_null($user = $this->getUser($credentials))) {
            return Contracts\VerificationBroker::INVALID_USER;
        }

        if (!$this->tokens->exists($user, $credentials['token'])) {
            return Contracts\VerificationBroker::INVALID_TOKEN;
        }

        return $user;
    }

    /**
     * Get the user for the given credentials.
     *
     * @param array $credentials
     * @return \Krucas\LaravelUserEmailVerification\Contracts\RequiresEmailVerification
     *
     * @throws \UnexpectedValueException
     */
    public function getUser(array $credentials)
    {
        $credentials = Arr::except($credentials, ['token']);

        $user = $this->users->retrieveByCredentials($credentials);

        if ($user && !$user instanceof Contracts\RequiresEmailVerification) {
            throw new UnexpectedValueException('User must implement RequiresEmailVerification interface.');
        }

        return $user;
    }

    /**
     * Get the verification token repository implementation.
     *
     * @return \Krucas\LaravelUserEmailVerification\Contracts\TokenRepositoryInterface
     */
    public function getRepository()
    {
        return $this->tokens;
    }
}

<?php

namespace Krucas\LaravelUserEmailVerification;

use Illuminate\Http\Request;
use Illuminate\Mail\Message;
use Illuminate\Support\Facades\Auth;
use Krucas\LaravelUserEmailVerification\Facades\Verification;

trait RegistersUsers
{
    use RedirectsUsers;

    /**
     * Handle a registration request for the application.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function register(Request $request)
    {
        $validator = $this->validator($request->all());

        if ($validator->fails()) {
            $this->throwValidationException($request, $validator);
        }

        $user = $this->create($request->all());

        $broker = $this->getBroker();

        $credentials = $request->only('email');

        Verification::broker($broker)->sendVerificationLink($credentials, function (Message $message) {
            $message->subject($this->getEmailSubject());
        });

        if (config('verification.verify')) {
            return redirect($this->verificationRedirectPath());
        }

        Auth::guard($this->getGuard())->login($user);

        return redirect($this->redirectPath());
    }

    /**
     * Get the broker to be used during verification process.
     *
     * @return string|null
     */
    public function getBroker()
    {
        return property_exists($this, 'broker') ? $this->broker : null;
    }

    /**
     * Get the guard to be used during verification.
     *
     * @return string|null
     */
    protected function getGuard()
    {
        return property_exists($this, 'guard') ? $this->guard : null;
    }

    /**
     * Get the e-mail subject line to be used for the reset link email.
     *
     * @return string
     */
    protected function getEmailSubject()
    {
        return trans('verification::verification.subject');
    }
}

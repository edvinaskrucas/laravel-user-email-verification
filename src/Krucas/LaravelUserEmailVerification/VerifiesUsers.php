<?php

namespace Krucas\LaravelUserEmailVerification;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Mail\Message;
use Illuminate\Support\Facades\Auth;
use Krucas\LaravelUserEmailVerification\Facades\Verification;
use Krucas\LaravelUserEmailVerification\Contracts;

trait VerifiesUsers
{
    use RedirectsUsers;

    /**
     * Display verify message / verify account.
     *
     * @param \Illuminate\Http\Request $request
     * @param string|null $token
     * @return \Illuminate\Http\Response
     */
    public function getVerify(Request $request, $token = null)
    {
        return $this->verify($request, $token);
    }

    /**
     * Display verify message / verify account.
     *
     * @param Request $request
     * @param null $token
     * @return \Illuminate\Http\Response
     */
    public function verify(Request $request, $token = null)
    {
        if (is_null($token)) {
            return $this->showVerifyMessage();
        }

        $credentials = ['email' => $request->get('email'), 'token' => $token];

        $broker = $this->getBroker();

        $response = Verification::broker($broker)->verify($credentials, function ($user) {
            $this->verifyUser($user);
        });

        switch ($response) {
            case Contracts\VerificationBroker::VERIFIED:
                return $this->getVerificationSuccessResponse($response);

            default:
                return $this->getVerificationFailureResponse($request, $response);
        }
    }

    /**
     * Verify user.
     *
     * @param \Krucas\LaravelUserEmailVerification\Contracts\RequiresEmailVerification $user
     * @return void
     */
    protected function verifyUser($user)
    {
        $user->verified = true;
        $user->verified_at = new Carbon('now');

        $user->save();

        Auth::guard($this->getGuard())->login($user);
    }

    /**
     * Display verify message.
     *
     * @return \Illuminate\Http\Response
     */
    public function showVerifyMessage()
    {
        return view('verification::auth.verification.message');
    }

    /**
     * Display link resend form.
     *
     * @return \Illuminate\Http\Response
     */
    public function getResend()
    {
        return $this->showResendForm();
    }

    /**
     * Display link resend form.
     *
     * @return \Illuminate\Http\Response
     */
    public function showResendForm()
    {
        return view('verification::auth.verification.resend');
    }

    /**
     * Send a verification link to the given user.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function postResend(Request $request)
    {
        return $this->sendVerificationLinkEmail($request);
    }

    /**
     * Send a verification link to the given user.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function sendVerificationLinkEmail(Request $request)
    {
        $this->validate($request, ['email' => 'required|email']);

        $broker = $this->getBroker();

        $credentials = $request->only('email');

        $response = Verification::broker($broker)->sendVerificationLink($credentials, function (Message $message) {
            $message->subject($this->getEmailSubject());
        });

        switch ($response) {
            case Contracts\VerificationBroker::VERIFICATION_LINK_SENT:
                return $this->getResendLinkEmailSuccessResponse($response);

            default:
                return $this->getResendLinkEmailFailureResponse($response);
        }
    }

    /**
     * Get the response for after the link could not be sent.
     *
     * @param string $response
     * @return \Symfony\Component\HttpFoundation\Response
     */
    protected function getResendLinkEmailFailureResponse($response)
    {
        return redirect()->back()->withErrors(['email' => trans($response)]);
    }

    /**
     * Get the response for after the link has been successfully sent.
     *
     * @param  string  $response
     * @return \Symfony\Component\HttpFoundation\Response
     */
    protected function getResendLinkEmailSuccessResponse($response)
    {
        return redirect()->back()->with('status', trans($response));
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

    /**
     * Get the response for after a successful verification.
     *
     * @param string $response
     * @return \Symfony\Component\HttpFoundation\Response
     */
    protected function getVerificationSuccessResponse($response)
    {
        return redirect($this->verificationRedirectPath())->with('status', trans($response));
    }

    /**
     * Get the response for after a failing verification.
     *
     * @param Request $request
     * @param string $response
     * @return \Symfony\Component\HttpFoundation\Response
     */
    protected function getVerificationFailureResponse(Request $request, $response)
    {
        return redirect()->route('verification.resend')->withErrors(['status' => trans($response)]);
    }

    /**
     * Get the guest middleware for the application.
     */
    public function guestMiddleware()
    {
        $guard = $this->getGuard();

        return $guard ? 'guest:'.$guard : 'guest';
    }
}

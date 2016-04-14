<?php

namespace Krucas\LaravelUserEmailVerification;

use Illuminate\Foundation\Auth\RedirectsUsers as LaravelRedirectsUsers;
use Illuminate\Http\Request;
use Krucas\LaravelUserEmailVerification\Contracts;
use Illuminate\Support\Facades\Auth;

trait AuthenticatesUsers
{
    use RedirectsUsers, LaravelRedirectsUsers;

    /**
     * Check if user is verified or not.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Krucas\LaravelUserEmailVerification\Contracts\RequiresEmailVerification $user
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function authenticated(Request $request, Contracts\RequiresEmailVerification $user)
    {
        if (config('verification.verify') && !$user->isUserEmailVerified()) {
            Auth::guard($this->getGuard())->logout();

            return redirect($this->verificationRedirectPath());
        }

        return redirect()->intended($this->redirectPath());
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
}

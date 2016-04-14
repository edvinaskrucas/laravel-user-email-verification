<?php

namespace Krucas\LaravelUserEmailVerification;

trait AuthenticatesAndRegistersUsers
{
    use AuthenticatesUsers, RegistersUsers {
        AuthenticatesUsers::verificationRedirectPath insteadof RegistersUsers;
        AuthenticatesUsers::getGuard insteadof RegistersUsers;
    }
}

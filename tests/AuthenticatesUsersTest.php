<?php

namespace Krucas\LaravelUserEmailVerification\Test;

use Mockery as m;
use Illuminate\Support\Facades\Auth;

class AuthenticatesUsersTest extends TestCase
{
    public function tearDown()
    {
        parent::tearDown();
        m::close();
    }

    public function testShouldLogoutAndRedirect()
    {
        $user = $this->getUserMock();
        $user->shouldReceive('isUserEmailVerified')->andReturn(false);

        $this->laravelContainer->shouldReceive('make')->with('config', [])->andReturn($config = $this->getConfigMock());
        $config->shouldReceive('get')->with('verification.verify', null)->andReturn(true);

        $this->laravelContainer->shouldReceive('make')->with('redirect', [])->andReturn(
            $redirect = $this->getRedirectMock()
        );
        $redirect->shouldReceive('to')->once()->andReturn('redirect');

        Auth::shouldReceive('guard')->once()->andReturn($guard = m::mock('Illuminate\Contracts\Auth\Guard'));
        $guard->shouldReceive('logout')->once();

        $this->laravelContainer->shouldReceive('make')->with('url', [])->andReturn($url = $this->getUrlMock());
        $url->shouldReceive('route')->andReturn('redirect');

        $trait = $this->getObjectForTrait('Krucas\LaravelUserEmailVerification\AuthenticatesUsers');

        $this->assertEquals('redirect', $trait->authenticated($this->getRequestMock(), $user));
    }

    public function testShouldRedirectIntendedOnConfigEnabledUserVerified()
    {
        $user = $this->getUserMock();
        $user->shouldReceive('isUserEmailVerified')->andReturn(true);

        $this->laravelContainer->shouldReceive('make')->with('config', [])->andReturn($config = $this->getConfigMock());
        $config->shouldReceive('get')->with('verification.verify', null)->andReturn(true);

        $this->laravelContainer->shouldReceive('make')->with('redirect', [])->andReturn(
            $redirect = $this->getRedirectMock()
        );
        $redirect->shouldReceive('intended')->once()->andReturn('intended');

        $trait = $this->getObjectForTrait('Krucas\LaravelUserEmailVerification\AuthenticatesUsers');

        $this->assertEquals('intended', $trait->authenticated($this->getRequestMock(), $user));
    }

    public function testShouldRedirectIntendedOnConfigDisabled()
    {
        $this->laravelContainer->shouldReceive('make')->with('config', [])->andReturn($config = $this->getConfigMock());
        $config->shouldReceive('get')->with('verification.verify', null)->andReturn(false);

        $this->laravelContainer->shouldReceive('make')->with('redirect', [])->andReturn(
            $redirect = $this->getRedirectMock()
        );
        $redirect->shouldReceive('intended')->once()->andReturn('intended');

        $trait = $this->getObjectForTrait('Krucas\LaravelUserEmailVerification\AuthenticatesUsers');

        $this->assertEquals('intended', $trait->authenticated($this->getRequestMock(), $this->getUserMock()));
    }
}

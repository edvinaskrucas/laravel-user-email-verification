<?php

namespace Krucas\LaravelUserEmailVerification\Test;

use Illuminate\Support\Facades\Facade;
use Mockery as m;
use Illuminate\Container\Container;

abstract class TestCase extends \PHPUnit_Framework_TestCase
{
    protected $laravelContainer;

    public function tearDown()
    {
        Facade::clearResolvedInstances();
    }

    protected function setUp()
    {
        parent::setUp();

        $this->laravelContainer = m::mock('Illuminate\Contracts\Container\Container');
        Container::setInstance($this->laravelContainer);
    }

    protected function getConfigMock()
    {
        return m::mock('Illuminate\Contracts\Config\Repository');
    }

    protected function getRedirectMock()
    {
        return m::mock('Illuminate\Routing\Redirector');
    }

    protected function getUrlMock()
    {
        return m::mock('Illuminate\Contracts\Routing\UrlGenerator');
    }

    protected function getRequestMock()
    {
        return m::mock('Illuminate\Http\Request');
    }

    protected function getUserMock()
    {
        return m::mock('Krucas\LaravelUserEmailVerification\Contracts\RequiresEmailVerification');
    }

    protected function getBrokerMock()
    {
        return m::mock('Krucas\LaravelUserEmailVerification\Contracts\VerificationBroker');
    }

    protected function getGuardMock()
    {
        return m::mock('Illuminate\Contracts\Auth\Guard');
    }

    protected function getViewMock()
    {
        return m::mock('Illuminate\Contracts\View\Factory');
    }
}

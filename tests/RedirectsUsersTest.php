<?php

namespace Krucas\LaravelUserEmailVerification\Test;

use Mockery as m;

class RedirectsUsersTest extends TestCase
{
    public function tearDown()
    {
        parent::tearDown();
        m::close();
    }

    public function testVerificationRedirectPathFromProperty()
    {
        $trait = $this->getObjectForTrait('Krucas\LaravelUserEmailVerification\RedirectsUsers');
        $trait->verificationRedirectPath = 'pathFrom';

        $this->assertEquals('pathFrom', $trait->verificationRedirectPath());
    }

    public function testVerificationRedirectToFromProperty()
    {
        $trait = $this->getObjectForTrait('Krucas\LaravelUserEmailVerification\RedirectsUsers');
        $trait->verificationRedirectTo = 'pathTo';

        $this->assertEquals('pathTo', $trait->verificationRedirectPath());
    }

    public function testVerificationRedirectPathFromRoute()
    {
        $this->laravelContainer->shouldReceive('make')->with('url', [])->andReturn($url = $this->getUrlMock());
        $url->shouldReceive('route')->andReturn('routePath');

        $trait = $this->getObjectForTrait('Krucas\LaravelUserEmailVerification\RedirectsUsers');

        $this->assertEquals('routePath', $trait->verificationRedirectPath());
    }
}

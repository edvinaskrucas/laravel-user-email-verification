<?php

namespace Krucas\LaravelUserEmailVerification\Test;

use Mockery as m;
use Illuminate\Support\Facades\Auth;
use Krucas\LaravelUserEmailVerification\Facades\Verification;

class RegistersUsersTest extends TestCase
{
    public function tearDown()
    {
        parent::tearDown();
        m::close();
    }

    public function testRegisterShouldRedirectToVerificationPage()
    {
        $trait = $this->getTraitMock(['validator', 'create', 'verificationRedirectPath']);
        $trait->expects($this->once())->method('validator')->will($this->returnValue(
            $validator = m::mock('stdClass')
        ));
        $trait->expects($this->once())->method('verificationRedirectPath')->will($this->returnValue('verifyPath'));
        $validator->shouldReceive('fails')->once()->andReturn(false);

        $request = $this->getRequestMock();
        $request->shouldReceive('all')->andReturn(['email' => 'mail']);
        $request->shouldReceive('only')->with('email')->andReturn(['email' => 'mail']);

        Verification::shouldReceive('broker')->once()->andReturn($broker = $this->getBrokerMock());
        $broker->shouldReceive('sendVerificationLink')->once();

        $this->laravelContainer->shouldReceive('make')->with('config', [])->andReturn($config = $this->getConfigMock());
        $config->shouldReceive('get')->with('verification.verify', null)->andReturn(true);

        $this->laravelContainer->shouldReceive('make')->with('redirect', [])->andReturn(
            $redirect = $this->getRedirectMock()
        );
        $redirect->shouldReceive('to')->once()->with('verifyPath', 302, [], null)->andReturn('redirect');

        $this->assertEquals('redirect', $trait->register($request));
    }

    public function testRegisterShouldRedirectToRedirectPath()
    {

        $trait = $this->getTraitMock(['validator', 'create', 'redirectPath']);
        $trait->expects($this->once())->method('validator')->will($this->returnValue(
            $validator = m::mock('stdClass')
        ));
        $trait->expects($this->once())->method('redirectPath')->will($this->returnValue('redirectPath'));
        $validator->shouldReceive('fails')->once()->andReturn(false);

        $request = $this->getRequestMock();
        $request->shouldReceive('all')->andReturn(['email' => 'mail']);
        $request->shouldReceive('only')->with('email')->andReturn(['email' => 'mail']);

        Verification::shouldReceive('broker')->once()->andReturn($broker = $this->getBrokerMock());
        $broker->shouldReceive('sendVerificationLink')->once();

        $this->laravelContainer->shouldReceive('make')->with('config', [])->andReturn($config = $this->getConfigMock());
        $config->shouldReceive('get')->with('verification.verify', null)->andReturn(false);

        $this->laravelContainer->shouldReceive('make')->with('redirect', [])->andReturn(
            $redirect = $this->getRedirectMock()
        );
        $redirect->shouldReceive('to')->once()->with('redirectPath', 302, [], null)->andReturn('redirect');

        Auth::shouldReceive('guard')->andReturn($guard = $this->getGuardMock());
        $guard->shouldReceive('login')->once();

        $this->assertEquals('redirect', $trait->register($request));
    }

    protected function getTraitMock($methods = [])
    {
        return $this->getMockForTrait(
            'Krucas\LaravelUserEmailVerification\RegistersUsers',
            [],
            '',
            true,
            true,
            true,
            $methods
        );
    }
}

<?php

namespace Krucas\LaravelUserEmailVerification\Test;

use Illuminate\Contracts\View\Factory;
use Krucas\LaravelUserEmailVerification\Contracts\VerificationBroker;
use Mockery as m;
use Illuminate\Support\Facades\Auth;
use Krucas\LaravelUserEmailVerification\Facades\Verification;

class VerifiesUsersTest extends TestCase
{
    public function tearDown()
    {
        parent::tearDown();
        m::close();
    }

    public function testVerifyShouldShowMessageFormWhenNoTokenIsProvided()
    {
        $trait = $this->getTraitMock();

        $this->laravelContainer->shouldReceive('make')->with(Factory::class, [])->andReturn(
            $view = $this->getViewMock()
        );
        $view->shouldReceive('make')->with('verification::auth.verification.message', [], [])->andReturn('view');

        $this->assertEquals('view', $trait->getVerify($this->getRequestMock(), null));
    }

    public function testVerifyShouldShowSuccessForm()
    {
        $trait = $this->getTraitMock(['verificationRedirectPath']);
        $trait->expects($this->once())->method('verificationRedirectPath')->will($this->returnValue('path'));

        Verification::shouldReceive('broker')->andReturn($broker = $this->getBrokerMock());
        $broker->shouldReceive('verify')->once()->andReturn(VerificationBroker::VERIFIED);

        $this->laravelContainer->shouldReceive('make')->with('redirect', [])->andReturn(
            $redirect = $this->getRedirectMock()
        );
        $redirect->shouldReceive('to')->once()->with('path', 302, [], null)->andReturn($response = m::mock('stdClass'));
        $response->shouldReceive('with')->once()->with('status', 'translated')->andReturn('redirect');

        $this->laravelContainer->shouldReceive('make')->with('translator', [])->andReturn(
            $translator = m::mock('stdClass')
        );
        $translator->shouldReceive('trans')->andReturn('translated');

        $request = $this->getRequestMock();
        $request->shouldReceive('get');

        $this->assertEquals('redirect', $trait->getVerify($request, 'token'));
    }

    public function testVerifyShouldShowFailureForm()
    {
        $trait = $this->getTraitMock();

        Verification::shouldReceive('broker')->andReturn($broker = $this->getBrokerMock());
        $broker->shouldReceive('verify')->once()->andReturn(VerificationBroker::INVALID_TOKEN);

        $this->laravelContainer->shouldReceive('make')->with('redirect', [])->andReturn(
            $redirect = $this->getRedirectMock()
        );
        $redirect->shouldReceive('route')->once()->with('verification.resend')->andReturn(
            $response = m::mock('stdClass')
        );
        $response->shouldReceive('withErrors')->once()->with(['status' => 'translated'])->andReturn('redirect');

        $this->laravelContainer->shouldReceive('make')->with('translator', [])->andReturn(
            $translator = m::mock('stdClass')
        );
        $translator->shouldReceive('trans')->andReturn('translated');

        $request = $this->getRequestMock();
        $request->shouldReceive('get');

        $this->assertEquals('redirect', $trait->getVerify($request, 'token'));
    }

    public function testShowResendForm()
    {
        $trait = $this->getTraitMock();

        $this->laravelContainer->shouldReceive('make')->with(Factory::class, [])->andReturn(
            $view = $this->getViewMock()
        );
        $view->shouldReceive('make')->with('verification::auth.verification.resend', [], [])->andReturn('view');

        $this->assertEquals('view', $trait->getResend());
    }

    public function testSendVerificationLinkShowSuccess()
    {
        $trait = $this->getTraitMock(['validate']);
        $trait->expects($this->once())->method('validate');

        $request = $this->getRequestMock();
        $request->shouldReceive('only')->with('email')->andReturn(['email' => 'mail']);

        Verification::shouldReceive('broker')->andReturn($broker = $this->getBrokerMock());
        $broker->shouldReceive('sendVerificationLink')->once()->andReturn(VerificationBroker::VERIFICATION_LINK_SENT);

        $this->laravelContainer->shouldReceive('make')->with('redirect', [])->andReturn(
            $redirect = $this->getRedirectMock()
        );
        $redirect->shouldReceive('back')->andReturn($response = m::mock('stdClass'));
        $response->shouldReceive('with')->with('status', 'translated')->andReturn('redirect');

        $this->laravelContainer->shouldReceive('make')->with('translator', [])->andReturn(
            $translator = m::mock('stdClass')
        );
        $translator->shouldReceive('trans')->andReturn('translated');

        $this->assertEquals('redirect', $trait->postResend($request));
    }

    public function testSendVerificationLinkShowFailure()
    {
        $trait = $this->getTraitMock(['validate']);
        $trait->expects($this->once())->method('validate');

        $request = $this->getRequestMock();
        $request->shouldReceive('only')->with('email')->andReturn(['email' => 'mail']);

        Verification::shouldReceive('broker')->andReturn($broker = $this->getBrokerMock());
        $broker->shouldReceive('sendVerificationLink')->once()->andReturn(VerificationBroker::INVALID_USER);

        $this->laravelContainer->shouldReceive('make')->with('redirect', [])->andReturn(
            $redirect = $this->getRedirectMock()
        );
        $redirect->shouldReceive('back')->andReturn($response = m::mock('stdClass'));
        $response->shouldReceive('withErrors')->with(['email' => 'translated'])->andReturn('redirect');

        $this->laravelContainer->shouldReceive('make')->with('translator', [])->andReturn(
            $translator = m::mock('stdClass')
        );
        $translator->shouldReceive('trans')->andReturn('translated');

        $this->assertEquals('redirect', $trait->postResend($request));
    }

    protected function getTraitMock($methods = [])
    {
        return $this->getMockForTrait(
            'Krucas\LaravelUserEmailVerification\VerifiesUsers',
            [],
            '',
            true,
            true,
            true,
            $methods
        );
    }
}

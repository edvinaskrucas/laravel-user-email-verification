<?php

namespace Krucas\LaravelUserEmailVerification\Test;

use Mockery as m;
use Krucas\LaravelUserEmailVerification\VerificationBroker;

class VerificationBrokerTest extends \PHPUnit_Framework_TestCase
{
    public function tearDown()
    {
        m::close();
    }

    public function testIfUserIsNotFoundErrorRedirectIsReturned()
    {
        $mocks = $this->getMocks();
        $broker = m::mock('Krucas\LaravelUserEmailVerification\VerificationBroker[getUser]', array_values($mocks));
        $broker->shouldReceive('getUser')->once()->andReturnNull();
        $this->assertEquals(VerificationBroker::INVALID_USER, $broker->sendVerificationLink(['credentials']));
    }

    /**
     * @expectedException \UnexpectedValueException
     */
    public function testGetUserThrowsExceptionIfUserDoesntImplementRequiresEmailVerification()
    {
        $broker = $this->getBroker($mocks = $this->getMocks());
        $mocks['users']->shouldReceive('retrieveByCredentials')->once()->with(['foo'])->andReturn('bar');
        $broker->getUser(['foo']);
    }

    public function testUserIsRetrievedByCredentials()
    {
        $broker = $this->getBroker($mocks = $this->getMocks());
        $mocks['users']->shouldReceive('retrieveByCredentials')->once()->with(['foo'])->andReturn(
            $user = m::mock('Krucas\LaravelUserEmailVerification\Contracts\RequiresEmailVerification')
        );
        $this->assertEquals($user, $broker->getUser(['foo']));
    }

    public function testBrokerCreatesTokenAndRedirectsWithoutError()
    {
        $mocks = $this->getMocks();
        $broker = m::mock(
            'Krucas\LaravelUserEmailVerification\VerificationBroker[emailVerificationLink]',
            array_values($mocks)
        );
        $mocks['users']->shouldReceive('retrieveByCredentials')->once()->with(['foo'])->andReturn(
            $user = m::mock('Krucas\LaravelUserEmailVerification\Contracts\RequiresEmailVerification')
        );
        $mocks['tokens']->shouldReceive('create')->once()->with($user)->andReturn('token');
        $callback = function () {};
        $broker->shouldReceive('emailVerificationLink')->once()->with($user, 'token', $callback);
        $this->assertEquals(VerificationBroker::VERIFICATION_LINK_SENT, $broker->sendVerificationLink(
            ['foo'],
            $callback
        ));
    }

    public function testMailerIsCalledWithProperViewTokenAndCallback()
    {
        unset($_SERVER['__verification.verify.test']);
        $broker = $this->getBroker($mocks = $this->getMocks());
        $callback = function ($message, $user) {
            $_SERVER['__verification.verify.test'] = true;
        };
        $user = m::mock('Krucas\LaravelUserEmailVerification\Contracts\RequiresEmailVerification');
        $mocks['mailer']->shouldReceive('send')->once()->with(
            'verifyAccountView',
            ['token' => 'token', 'user' => $user],
            m::type('Closure')
        )->andReturnUsing(function ($view, $data, $callback) {
            return $callback;
        });
        $user->shouldReceive('getEmailForVerification')->once()->andReturn('email');
        $message = m::mock('StdClass');
        $message->shouldReceive('to')->once()->with('email');
        $result = $broker->emailVerificationLink($user, 'token', $callback);
        call_user_func($result, $message);
        $this->assertTrue($_SERVER['__verification.verify.test']);
    }

    public function testRedirectIsReturnedByVerificationWhenUserCredentialsInvalid()
    {
        $broker = $this->getBroker($mocks = $this->getMocks());
        $mocks['users']->shouldReceive('retrieveByCredentials')->once()->with(['creds'])->andReturn(null);
        $this->assertEquals(VerificationBroker::INVALID_USER, $broker->verify(['creds'], function () {}));
    }

    public function testRedirectReturnedByVerificationWhenRecordDoesntExistInTable()
    {
        $creds = ['token' => 'token'];
        $broker = $this->getBroker($mocks = $this->getMocks());
        $mocks['users']
            ->shouldReceive('retrieveByCredentials')
            ->once()
            ->with(array_except($creds, ['token']))
            ->andReturn($user = m::mock('Krucas\LaravelUserEmailVerification\Contracts\RequiresEmailVerification'));
        $mocks['tokens']->shouldReceive('exists')->with($user, 'token')->andReturn(false);
        $this->assertEquals(VerificationBroker::INVALID_TOKEN, $broker->verify($creds, function () {}));
    }

    public function testVerificationRemovesRecordOnTokenTableAndCallsCallback()
    {
        unset($_SERVER['__verification.verify.test']);
        $broker = $this->getMock(
            'Krucas\LaravelUserEmailVerification\VerificationBroker',
            ['validateVerification'],
            array_values($mocks = $this->getMocks())
        );
        $broker
            ->expects($this->once())
            ->method('validateVerification')
            ->will(
                $this->returnValue(
                    $user = m::mock('Krucas\LaravelUserEmailVerification\Contracts\RequiresEmailVerification')
                )
            );
        $mocks['tokens']->shouldReceive('delete')->once()->with('token');
        $callback = function ($user) {
            $_SERVER['__verification.verify.test'] = compact('user');
            return 'foo';
        };
        $this->assertEquals(VerificationBroker::VERIFIED, $broker->verify(['token' => 'token'], $callback));
        $this->assertEquals(['user' => $user], $_SERVER['__verification.verify.test']);
    }

    protected function getBroker($mocks)
    {
        return new VerificationBroker(
            $mocks['tokens'],
            $mocks['users'],
            $mocks['mailer'],
            $mocks['view']
        );
    }

    protected function getMocks()
    {
        $mocks = [
            'tokens' => m::mock('Krucas\LaravelUserEmailVerification\Contracts\TokenRepositoryInterface'),
            'users'  => m::mock('Illuminate\Contracts\Auth\UserProvider'),
            'mailer' => m::mock('Illuminate\Contracts\Mail\Mailer'),
            'view'   => 'verifyAccountView',
        ];

        return $mocks;
    }
}

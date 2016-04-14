<?php

namespace Krucas\LaravelUserEmailVerification\Test;

use Carbon\Carbon;
use Krucas\LaravelUserEmailVerification\DatabaseTokenRepository;
use Mockery as m;

class DatabaseTokenRepositoryTest extends \PHPUnit_Framework_TestCase
{
    public function tearDown()
    {
        m::close();
    }

    public function testCreateShouldDeleteExistingAndInsertNewOne()
    {
        $repository = $this->getRepository($mocks = $this->getMocks());

        $user = $this->getUserMock();
        $user->shouldReceive('getEmailForVerification')->andReturn('mail');

        $mocks['connection']->shouldReceive('table')->andReturn($table = $this->getQueryBuilderMock());
        $table->shouldReceive('where')->once()->with('email', 'mail')->andReturn($query = $this->getQueryBuilderMock());
        $query->shouldReceive('delete')->once();
        $table->shouldReceive('insert')->once();

        $repository->create($user);
    }

    public function testExistsShouldReturnTrue()
    {
        $repository = $this->getRepository($mocks = $this->getMocks());

        $user = $this->getUserMock();
        $user->shouldReceive('getEmailForVerification')->andReturn('mail');

        $mocks['connection']->shouldReceive('table')->andReturn($table = $this->getQueryBuilderMock());
        $table->shouldReceive('where')->once()->with('email', 'mail')->andReturn($query = $this->getQueryBuilderMock());
        $query->shouldReceive('where')->once()->with('token', 'token')->andReturn(
            $entryQuery = $this->getQueryBuilderMock()
        );
        $entryQuery->shouldReceive('first')->andReturn(['created' => time()]);

        $this->assertTrue($repository->exists($user, 'token'));
    }

    public function testExistsShouldReturnFalseOnNotFound()
    {
        $repository = $this->getRepository($mocks = $this->getMocks());

        $user = $this->getUserMock();
        $user->shouldReceive('getEmailForVerification')->andReturn('mail');

        $mocks['connection']->shouldReceive('table')->andReturn($table = $this->getQueryBuilderMock());
        $table->shouldReceive('where')->once()->with('email', 'mail')->andReturn($query = $this->getQueryBuilderMock());
        $query->shouldReceive('where')->once()->with('token', 'token')->andReturn(
            $entryQuery = $this->getQueryBuilderMock()
        );
        $entryQuery->shouldReceive('first')->andReturnNull();

        $this->assertFalse($repository->exists($user, 'token'));
    }

    public function testExistsOnExpiredToken()
    {
        $mocks = $this->getMocks();
        $mocks['expires'] = 1;

        $repository = $this->getRepository($mocks);

        $user = $this->getUserMock();
        $user->shouldReceive('getEmailForVerification')->andReturn('mail');

        $mocks['connection']->shouldReceive('table')->andReturn($table = $this->getQueryBuilderMock());
        $table->shouldReceive('where')->once()->with('email', 'mail')->andReturn($query = $this->getQueryBuilderMock());
        $query->shouldReceive('where')->once()->with('token', 'token')->andReturn(
            $entryQuery = $this->getQueryBuilderMock()
        );
        $entryQuery->shouldReceive('first')->andReturn(['created_at' => (new Carbon('-1 day'))->format('Y-m-d H:i:s')]);

        $this->assertFalse($repository->exists($user, 'token'));
    }

    public function testExistsOnNonExpiredToken()
    {
        $mocks = $this->getMocks();
        $mocks['expires'] = 100;

        $repository = $this->getRepository($mocks);

        $user = $this->getUserMock();
        $user->shouldReceive('getEmailForVerification')->andReturn('mail');

        $mocks['connection']->shouldReceive('table')->andReturn($table = $this->getQueryBuilderMock());
        $table->shouldReceive('where')->once()->with('email', 'mail')->andReturn($query = $this->getQueryBuilderMock());
        $query->shouldReceive('where')->once()->with('token', 'token')->andReturn(
            $entryQuery = $this->getQueryBuilderMock()
        );
        $entryQuery->shouldReceive('first')->andReturn(['created_at' => (new Carbon())->format('Y-m-d H:i:s')]);

        $this->assertTrue($repository->exists($user, 'token'));
    }

    public function testDeleteShouldDeleteToken()
    {
        $repository = $this->getRepository($mocks = $this->getMocks());

        $mocks['connection']->shouldReceive('table')->andReturn($table = $this->getQueryBuilderMock());
        $table->shouldReceive('where')->once()->with('token', 'token')->andReturn($query = $this->getQueryBuilderMock());
        $query->shouldReceive('delete')->once();

        $repository->delete('token');
    }

    public function testDeleteShouldDeleteExpiredTokens()
    {
        $mocks = $this->getMocks();
        $mocks['expires'] = 100;
        $repository = $this->getRepository($mocks);

        $mocks['connection']->shouldReceive('table')->andReturn($table = $this->getQueryBuilderMock());
        $table->shouldReceive('where')->once()->andReturn($query = $this->getQueryBuilderMock());
        $query->shouldReceive('delete')->once();

        $repository->deleteExpired();
    }

    public function testDeleteShouldSkipDeleteExpiredIfDisabled()
    {
        $repository = $this->getRepository($mocks = $this->getMocks());

        $mocks['connection']->shouldReceive('table')->never();

        $repository->deleteExpired();
    }

    protected function getRepository($mocks)
    {
        return new DatabaseTokenRepository($mocks['connection'], $mocks['table'], $mocks['hashKey'], $mocks['expires']);
    }

    protected function getMocks()
    {
        $mocks = [
            'connection' => m::mock('Illuminate\Database\ConnectionInterface'),
            'table' => 'verifications',
            'hashKey' => 'hasKey',
            'expires' => 0,
        ];

        return $mocks;
    }

    protected function getUserMock()
    {
        return m::mock('Krucas\LaravelUserEmailVerification\Contracts\RequiresEmailVerification');
    }

    protected function getQueryBuilderMock()
    {
        return m::mock('Illuminate\Database\Query\Builder');
    }
}

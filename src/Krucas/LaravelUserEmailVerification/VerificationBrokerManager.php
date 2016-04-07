<?php

namespace Krucas\LaravelUserEmailVerification;

use Closure;
use InvalidArgumentException;
use Krucas\LaravelUserEmailVerification\Contracts\Factory;

class VerificationBrokerManager implements Factory
{
    /**
     * The application instance.
     *
     * @var \Illuminate\Foundation\Application
     */
    protected $app;

    /**
     * The array of resolved repositories.
     *
     * @var array
     */
    protected $brokers = [];

    /**
     * The registered custom driver creators.
     *
     * @var array
     */
    protected $customCreators = [];

    /**
     * Create a new Factory instance.
     *
     * @param \Illuminate\Foundation\Application $app
     */
    public function __construct($app)
    {
        $this->app = $app;
    }

    /**
     * Get a verification broker instance by name.
     *
     * @param string|null $name
     * @return mixed
     */
    public function broker($name = null)
    {
        $name = $name ?: $this->getDefaultDriver();

        return $this->brokers[$name] = $this->get($name);
    }

    /**
     * Attempt to get the broker from the local cache.
     *
     * @param string $name
     * @return \Krucas\LaravelUserEmailVerification\Contracts\VerificationBroker
     */
    protected function get($name)
    {
        return isset($this->brokers[$name]) ? $this->brokers[$name] : $this->resolve($name);
    }

    /**
     * Resolve the given broker.
     *
     * @param string $name
     * @return \Krucas\LaravelUserEmailVerification\Contracts\VerificationBroker
     */
    protected function resolve($name)
    {
        $config = $this->getConfig($name);

        if (is_null($config)) {
            throw new InvalidArgumentException("Verification broker [{$name}] is not defined.");
        }

        if (isset($this->customCreators[$name])) {
            return $this->callCustomCreator($name, $config);
        } else {
            return $this->{'create'.ucfirst($name).'Broker'}($config);
        }
    }

    /**
     * Call a custom driver creator.
     *
     * @param string $name
     * @param array $config
     * @return mixed
     */
    protected function callCustomCreator($name, array $config)
    {
        return $this->customCreators[$name]($this->app, $config);
    }

    /**
     * Create users broker.
     *
     * @param array $config
     * @return \Krucas\LaravelUserEmailVerification\Contracts\VerificationBroker
     */
    protected function createUsersBroker(array $config)
    {
        return new VerificationBroker(
            $this->createTokenRepository($this->app['config']['verification.repositories.'.$config['repository']]),
            $this->app['auth']->createUserProvider($config['provider']),
            $this->app['mailer'],
            $config['email']
        );
    }

    /**
     * Create token repository
     *
     * @param array $config
     * @return \Krucas\LaravelUserEmailVerification\Contracts\TokenRepositoryInterface
     */
    protected function createTokenRepository(array $config)
    {
        return $this->{'create'.ucfirst($config['driver']).'Repository'}($config);
    }

    /**
     * Create database token repository.
     *
     * @param array $config
     * @return \Krucas\LaravelUserEmailVerification\Contracts\TokenRepositoryInterface
     */
    protected function createDatabaseRepository(array $config)
    {
        return new DatabaseTokenRepository(
            $this->app['db']->connection($config['connection']),
            $config['table'],
            $this->app['config']['app.key'],
            $config['expires']
        );
    }

    /**
     * Get the broker configuration.
     *
     * @param string $name
     * @return array
     */
    protected function getConfig($name)
    {
        return $this->app['config']["verification.brokers.{$name}"];
    }

    /**
     * Get the default settings repository name.
     *
     * @return string
     */
    public function getDefaultDriver()
    {
        return $this->app['config']['verification.default'];
    }

    /**
     * Set the default driver name.
     *
     * @param string $name
     * @return void
     */
    public function setDefaultDriver($name)
    {
        $this->app['config']['verification.default'] = $name;
    }

    /**
     * Register a custom driver creator Closure.
     *
     * @param string $driver
     * @param \Closure $callback
     * @return $this
     */
    public function extend($driver, Closure $callback)
    {
        $this->customCreators[$driver] = $callback;

        return $this;
    }

    /**
     * Dynamically call the default driver instance.
     *
     * @param string $method
     * @param array $parameters
     * @return mixed
     */
    public function __call($method, $parameters)
    {
        return call_user_func_array([$this->broker(), $method], $parameters);
    }
}

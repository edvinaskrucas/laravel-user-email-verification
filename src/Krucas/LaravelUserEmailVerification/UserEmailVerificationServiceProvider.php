<?php

namespace Krucas\LaravelUserEmailVerification;

use Illuminate\Support\ServiceProvider;
use Krucas\LaravelUserEmailVerification\Console\ClearVerificationTokensCommand;
use Krucas\LaravelUserEmailVerification\Console\MakeVerificationCommand;
use Krucas\LaravelUserEmailVerification\Contracts;

class UserEmailVerificationServiceProvider extends ServiceProvider
{
    /**
     * Aliases to be registered.
     *
     * @var array
     */
    protected $aliases = [
        'auth.verification' => [VerificationBrokerManager::class, Contracts\Factory::class],
        'auth.verification.broker' => [VerificationBroker::class, Contracts\VerificationBroker::class],
    ];

    /**
     * Boot service provider.
     *
     * @return void
     */
    public function boot()
    {
        $this->loadTranslationsFrom(__DIR__ . '/../../translations', 'verification');
        $this->loadViewsFrom(__DIR__ . '/../../views', 'verification');

        $this->publishes([
            __DIR__ . '/../../config/verification.php' => config_path('verification.php'),
        ], 'config');

        $this->publishes([
            __DIR__ . '/../../translations' => resource_path('lang/vendor/verification'),
        ], 'translations');

        $this->publishes([
            __DIR__ . '/../../views' => resource_path('views/vendor/verification'),
        ], 'views');
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(__DIR__ . '/../../config/verification.php', 'verification');

        $this->app->singleton('auth.verification', function ($app) {
            return new VerificationBrokerManager($app);
        });

        $this->app->bind('auth.verification.broker', function ($app) {
            return $app->make('auth.verification')->broker();
        });

        foreach ($this->aliases as $key => $aliases) {
            foreach ($aliases as $alias) {
                $this->app->alias($key, $alias);
            }
        }

        $this->registerCommands();
    }

    /**
     * Register the related console commands.
     *
     * @return void
     */
    protected function registerCommands()
    {
        $this->app->singleton('command.verification.make', function ($app) {
            return new MakeVerificationCommand($app['composer']);
        });

        $this->app->singleton('command.verification.clear', function ($app) {
            return new ClearVerificationTokensCommand();
        });

        $this->commands('command.verification.make');
        $this->commands('command.verification.clear');
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return [
            'command.verification.make',
            'command.verification.clear',
            'auth.verification',
            'auth.verification.broker',
            VerificationBrokerManager::class,
            Contracts\Factory::class,
            VerificationBroker::class,
            Contracts\VerificationBroker::class,
        ];
    }
}

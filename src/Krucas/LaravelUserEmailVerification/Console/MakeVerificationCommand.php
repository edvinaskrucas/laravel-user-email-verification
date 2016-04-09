<?php

namespace Krucas\LaravelUserEmailVerification\Console;

use Illuminate\Console\AppNamespaceDetectorTrait;
use Illuminate\Console\Command;
use Illuminate\Support\Composer;

class MakeVerificationCommand extends Command
{
    use AppNamespaceDetectorTrait;

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'verification:make';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Scaffold verification migrations, views and routes';

    /**
     * @var \Illuminate\Support\Composer
     */
    protected $composer;

    /**
     * Create a new settings table command instance.
     *
     * @param \Illuminate\Support\Composer $composer
     */
    public function __construct(Composer $composer)
    {
        parent::__construct();

        $this->composer = $composer;
    }

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function fire()
    {
        $this->createMigrations();

        $this->info('Installed VerifyController');

        file_put_contents(
            app_path('Http/Controllers/Auth/VerifyController.php'),
            $this->compileControllerStub()
        );

        $this->info('Updated routes.php');

        $this->appendRoutes();

        $this->composer->dumpAutoloads();
    }

    /**
     * Create migrations.
     *
     * @return void
     */
    protected function createMigrations()
    {
        file_put_contents(
            $this->createUsersVerificationsMigration(),
            file_get_contents(__DIR__.'/stubs/migrations/users_verifications.stub')
        );

        file_put_contents(
            $this->createUsersMigration(),
            file_get_contents(__DIR__.'/stubs/migrations/users.stub')
        );

        $this->info('Migrations created successfully!');
    }

    /**
     * Create a base migration file for the verifications table.
     *
     * @return string
     */
    protected function createUsersVerificationsMigration()
    {
        $name = 'create_users_verifications_table';

        $path = $this->laravel->databasePath().'/migrations';

        return $this->laravel['migration.creator']->create($name, $path);
    }

    /**
     * Create a base migration file for the users table.
     *
     * @return string
     */
    protected function createUsersMigration()
    {
        $name = 'add_verified_columns_to_users_table';

        $path = $this->laravel->databasePath().'/migrations';

        return $this->laravel['migration.creator']->create($name, $path);
    }

    /**
     * Compiles the VerifyController stub.
     *
     * @return string
     */
    protected function compileControllerStub()
    {
        return str_replace(
            '{{namespace}}',
            $this->getAppNamespace(),
            file_get_contents(__DIR__.'/stubs/controllers/verifycontroller.stub')
        );
    }

    /**
     * Append routes file.
     *
     * @return void
     */
    protected function appendRoutes()
    {
        $path = app_path('Http/routes.php');

        file_put_contents(
            $path,
            file_get_contents($path)."\n\n".file_get_contents(__DIR__.'/stubs/routes.stub')
        );
    }
}

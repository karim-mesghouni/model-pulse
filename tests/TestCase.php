<?php

namespace Tests;

use Illuminate\Config\Repository;
use Illuminate\Container\Container;
use Illuminate\Contracts\Debug\ExceptionHandler;
use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Events\Dispatcher;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Filesystem\FilesystemManager;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Facade;
use PHPUnit\Framework\TestCase as BaseTestCase;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\HttpFoundation\Response;
use Tests\Fixtures\Models\TestUser;
use Tests\Support\FakeAuthManager;
use Throwable;

class TestCase extends BaseTestCase
{
    protected Container $app;

    protected Capsule $capsule;

    protected function setUp(): void
    {
        parent::setUp();
        $this->bootContainer();
        $this->runPackageMigrations();
        $this->runFixtureMigrations();
    }

    protected function bootContainer(): void
    {
        $this->app = new Container();
        Container::setInstance($this->app);

        $rootPath = dirname(__DIR__);
        $storagePath = $rootPath.'/tests/tmp/storage';
        $s3Path = $rootPath.'/tests/tmp/s3-storage';
        if (! is_dir($storagePath)) {
            mkdir($storagePath, 0777, true);
        }
        if (! is_dir($s3Path)) {
            mkdir($s3Path, 0777, true);
        }

        $this->app->instance('config', new Repository([
            'database' => [
                'default' => 'testing',
                'connections' => [
                    'testing' => [
                        'driver' => 'sqlite',
                        'database' => ':memory:',
                        'prefix' => '',
                        'foreign_key_constraints' => true,
                    ],
                ],
            ],
            'filesystems' => [
                'default' => 'public',
                'disks' => [
                    'public' => [
                        'driver' => 'local',
                        'root' => $storagePath,
                        'url' => '/storage',
                        'visibility' => 'public',
                    ],
                    's3' => [
                        'driver' => 'local',
                        'root' => $s3Path,
                        'url' => '/s3-storage',
                        'visibility' => 'public',
                    ],
                ],
            ],
            'model-pulse' => [
                'attachments' => [
                    'disk' => null,
                ],
            ],
        ]));

        Facade::setFacadeApplication($this->app);

        $this->capsule = new Capsule($this->app);
        $this->capsule->addConnection($this->app['config']->get('database.connections.testing'));
        $this->capsule->setAsGlobal();
        $this->capsule->bootEloquent();

        $this->app->instance('db', $this->capsule->getDatabaseManager());
        $this->app->instance('db.connection', $this->capsule->getConnection());
        $this->app->instance('db.schema', $this->capsule->schema());
        $this->app->instance('events', new Dispatcher($this->app));
        Model::setEventDispatcher($this->app->make('events'));

        $this->app->instance('translator', new class
        {
            public function get(string $key, array $replace = [], ?string $locale = null, bool $fallback = true): string
            {
                return $key;
            }
        });

        $this->app->instance('files', new Filesystem());
        $this->app->instance('filesystem', new FilesystemManager($this->app));

        $auth = new FakeAuthManager();
        $this->app->instance('auth', $auth);
        Auth::swap($auth);

        $this->app->instance(ExceptionHandler::class, new class implements ExceptionHandler
        {
            public function report(Throwable $e): void {}

            public function shouldReport(Throwable $e): bool
            {
                return false;
            }

            public function render($request, Throwable $e): Response
            {
                return new Response();
            }

            public function renderForConsole($output, Throwable $e): void
            {
                if ($output instanceof OutputInterface) {
                    $output->writeln($e->getMessage());
                }
            }
        });
    }

    protected function runPackageMigrations(): void
    {
        $files = glob(dirname(__DIR__).'/database/migrations/*.php') ?: [];
        sort($files);

        foreach ($files as $file) {
            $migration = require $file;
            $migration->up();
        }
    }

    protected function runFixtureMigrations(): void
    {
        $schema = $this->capsule->schema();

        $schema->create('test_users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->timestamps();
        });

        $schema->create('test_projects', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('status')->nullable();
            $table->unsignedBigInteger('owner_id')->nullable();
            $table->json('meta')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    protected function actingAs(?TestUser $user = null): TestUser
    {
        $authUser = $user ?? TestUser::query()->create(['name' => 'Test User']);
        $this->app->make('auth')->setUser($authUser);

        return $authUser;
    }

    protected function logout(): void
    {
        $this->app->make('auth')->setUser(null);
    }
}

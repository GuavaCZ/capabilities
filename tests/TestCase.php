<?php

namespace Tests;

use Guava\Capabilities\CapabilitiesServiceProvider;
use Illuminate\Database\Eloquent\Factories\Factory;
use Orchestra\Testbench\TestCase as Orchestra;
use Tests\Fixtures\Models\Tenant;
use Tests\Fixtures\Models\User;

class TestCase extends Orchestra
{
    protected function setUp(): void
    {
        parent::setUp();

        Factory::guessFactoryNamesUsing(
            fn (
                string $modelName,
            ) => 'Tests\\Database\\Factories\\'.class_basename($modelName).'Factory'
        );
    }

    protected function getPackageProviders($app)
    {
        return [
            CapabilitiesServiceProvider::class,
        ];
    }

    public function getEnvironmentSetUp($app)
    {
        config()->set('database.default', 'testing');
        config()->set('capabilities.tenancy', true);
        config()->set('capabilities.user_class', User::class);
        config()->set('capabilities.tenant_class', Tenant::class);

        $app->useDatabasePath(__DIR__.'/Fixtures/database');
    }

    protected function defineDatabaseMigrations()
    {
        $this->loadMigrationsFrom(__DIR__ . '/Fixtures/database/migrations');
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
    }
}

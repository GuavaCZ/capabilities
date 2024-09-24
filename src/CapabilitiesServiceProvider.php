<?php

namespace Guava\Capabilities;

use Guava\Capabilities\Commands\SyncCapabilitiesCommand;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class CapabilitiesServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        /*
         * This class is a Package Service Provider
         *
         * More info: https://github.com/spatie/laravel-package-tools
         */
        $package
            ->name('capabilities')
            ->hasConfigFile(['capabilities'])
//            ->hasViews()
            ->hasMigration('create_capabilities_table')
            ->hasCommand(SyncCapabilitiesCommand::class)
        ;
    }

    public function packageRegistered(): void
    {
        $this->app->bind(Capabilities::class, function ($app) {
            return new Capabilities;
        });
    }
}

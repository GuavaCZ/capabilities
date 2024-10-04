<?php

namespace Guava\Capabilities;

use Arr;
use Guava\Capabilities\Auth\Capability;
use Guava\Capabilities\Commands\SyncCapabilitiesCommand;
use Guava\Capabilities\Managers\CapabilityManager;
use Guava\Capabilities\Managers\RoleManager;
use Illuminate\Foundation\Auth\User;
use Illuminate\Support\Facades\Gate;
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
        $this->app->bind(Capabilities::class, function () {
            return new Capabilities;
        });

        $this->app->bind(CapabilityManager::class, function () {
            return new CapabilityManager;
        });
        $this->app->bind(RoleManager::class, function () {
            return new RoleManager;
        });
    }

    public function packageBooted()
    {
        Gate::before(function (User $user, string $ability, array $arguments) {
            $record = Arr::first($arguments);

            if (! $record) {
                return null;
            }

            $policy = Gate::getPolicyFor($record);

            if ($policy && method_exists($policy, $ability)) {
                return null;
            }

            $capability = Capability::tryFrom($ability);

            if (method_exists($user, 'hasCapability')) {

                if ($capability) {
                    $capability = $capability->get($record);
                }

                return $user->hasCapability($capability ?? $ability) ?: null;
//                return $user->hasCapability($ability, ...$arguments) ?: null;
            }

            return null;
        });
    }
}

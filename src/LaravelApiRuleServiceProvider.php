<?php

namespace BrayanCaro\LaravelApiRule;

use BrayanCaro\LaravelApiRule\Commands\LaravelApiRuleCommand;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class LaravelApiRuleServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        /*
         * This class is a Package Service Provider
         *
         * More info: https://github.com/spatie/laravel-package-tools
         */
        $package
            ->name('laravel-api-rule')
            ->hasConfigFile()
            ->hasViews()
            ->hasMigration('create_laravel-api-rule_table')
            ->hasCommand(LaravelApiRuleCommand::class);
    }
}

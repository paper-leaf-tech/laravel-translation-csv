<?php

namespace PaperleafTech\LaravelTranslation;

use PaperleafTech\LaravelTranslation\Commands\PushCommand;
use PaperleafTech\LaravelTranslation\Commands\PullCommand;
use PaperleafTech\LaravelTranslation\Services\GoogleSheetsService;
use Spatie\LaravelPackageTools\Commands\InstallCommand;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class LaravelTranslationServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('laravel-translation')
            ->hasConfigFile('laravel-translation')
            ->hasCommands([
                PushCommand::class,
                PullCommand::class,
            ])
            ->hasInstallCommand(function (InstallCommand $command) {
                $command
                    ->publishConfigFile()
                    ->askToStarRepoOnGitHub('paper-leaf-tech/laravel-translation');
            });
    }

    public function packageRegistered(): void
    {
        // Register GoogleSheetsService as a singleton
        $this->app->singleton(GoogleSheetsService::class, function ($app) {
            return new GoogleSheetsService();
        });
    }
}

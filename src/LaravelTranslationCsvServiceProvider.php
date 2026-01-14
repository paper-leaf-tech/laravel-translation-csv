<?php

namespace PaperleafTech\LaravelTranslationCsv;

use PaperleafTech\LaravelTranslationCsv\Commands\ExportCommand;
use PaperleafTech\LaravelTranslationCsv\Commands\ImportCommand;
use Spatie\LaravelPackageTools\Commands\InstallCommand;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class LaravelTranslationCsvServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('laravel-translation-csv')
            ->hasConfigFile('laravel-translation-csv')
            ->hasCommands([
                ExportCommand::class,
                ImportCommand::class,
            ])
            ->hasInstallCommand(function (InstallCommand $command) {
                $command->publishConfigFile();
            });
    }
}

<?php

namespace Spatie\LaravelPdf;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use Spatie\LaravelPdf\Commands\LaravelPdfCommand;

class LaravelPdfServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        /*
         * This class is a Package Service Provider
         *
         * More info: https://github.com/spatie/laravel-package-tools
         */
        $package
            ->name('laravel-pdf')
            ->hasConfigFile()
            ->hasViews()
            ->hasMigration('create_laravel-pdf_table')
            ->hasCommand(LaravelPdfCommand::class);
    }
}

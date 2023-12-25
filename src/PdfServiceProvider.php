<?php

namespace Spatie\LaravelPdf;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use Spatie\LaravelPdf\Commands\LaravelPdfCommand;

class PdfServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('laravel-pdf')
            ->hasConfigFile()
            ->hasCommand(LaravelPdfCommand::class);
    }
}

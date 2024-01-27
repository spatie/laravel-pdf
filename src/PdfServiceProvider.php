<?php

namespace Spatie\LaravelPdf;

use Illuminate\Support\Facades\Blade;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class PdfServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package->name('laravel-pdf');
    }

    public function bootingPackage()
    {
        Blade::directive('pageBreak', function () {
            return "<?php echo '<div style=\"page-break-after: always;\"></div>'; ?>";
        });

        Blade::directive('pageNumber', function () {
            return "<?php echo '<span class=\"pageNumber\"></span>'; ?>";
        });

        Blade::directive('totalPages', function () {
            return "<?php echo '<span class=\"totalPages\"></span>'; ?>";
        });

        Blade::directive('printColor', function ($mode = 'exact') {
            return "<?php echo '<style>html {-webkit-print-color-adjust: $mode;}</style>'; ?>";
        });
    }
}

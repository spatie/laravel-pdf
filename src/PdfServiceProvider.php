<?php

namespace Spatie\LaravelPdf;

use Illuminate\Support\Facades\Blade;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use Spatie\LaravelPdf\Drivers\BrowsershotDriver;
use Spatie\LaravelPdf\Drivers\CloudflareDriver;
use Spatie\LaravelPdf\Drivers\DomPdfDriver;
use Spatie\LaravelPdf\Drivers\GotenbergDriver;
use Spatie\LaravelPdf\Drivers\PdfDriver;
use Spatie\LaravelPdf\Drivers\WeasyPrintDriver;
use Spatie\LaravelPdf\Exceptions\InvalidDriver;

class PdfServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('laravel-pdf')
            ->hasConfigFile('laravel-pdf');
    }

    public function registeringPackage(): void
    {
        $this->app->singleton('laravel-pdf.driver.browsershot', function () {
            return new BrowsershotDriver(config('laravel-pdf.browsershot', []));
        });

        $this->app->singleton('laravel-pdf.driver.cloudflare', function () {
            return new CloudflareDriver(config('laravel-pdf.cloudflare', []));
        });

        $this->app->singleton('laravel-pdf.driver.dompdf', function () {
            return new DomPdfDriver(config('laravel-pdf.dompdf', []));
        });

        $this->app->singleton('laravel-pdf.driver.weasyprint', function () {
            return new WeasyPrintDriver(config('laravel-pdf.weasyprint', []));
        });

        $this->app->singleton('laravel-pdf.driver.gotenberg', function () {
            return new GotenbergDriver(config('laravel-pdf.gotenberg', []));
        });

        $this->app->singleton(PdfDriver::class, function () {
            $driverName = config('laravel-pdf.driver', 'browsershot');

            return match ($driverName) {
                'browsershot' => app('laravel-pdf.driver.browsershot'),
                'cloudflare' => app('laravel-pdf.driver.cloudflare'),
                'dompdf' => app('laravel-pdf.driver.dompdf'),
                'gotenberg' => app('laravel-pdf.driver.gotenberg'),
                'weasyprint' => app('laravel-pdf.driver.weasyprint'),
                default => throw InvalidDriver::unknown($driverName),
            };
        });
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

        Blade::directive('inlinedImage', function ($url) {
            return "<?php
                \$url = \Illuminate\Support\Str::of($url)->trim(\"'\")->trim('\"')->value();

                if (! \Illuminate\Support\Str::of(\$url)->isUrl()) {
                    try {
                        \$content = file_get_contents(\$url);
                    } catch(\Exception \$exception) {
                        throw \Spatie\LaravelPdf\Exceptions\CouldNotLoadImage::notFound(\$exception->getMessage());
                    }
                } else {
                    \$response = \Illuminate\Support\Facades\Http::get(\$url);

                    if (! \$response->successful()) {
                        throw \Spatie\LaravelPdf\Exceptions\CouldNotLoadImage::fetchFailed(\$response->toException());
                    }

                    \$content = \$response->body();
                }

                \$mime = (new finfo(FILEINFO_MIME_TYPE))->buffer(\$content) ?: 'image/png';

                echo '<img src=\"data:'.\$mime.';base64,'.base64_encode(\$content).'\">';
            ?>";
        });
    }
}

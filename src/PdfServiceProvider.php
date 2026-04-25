<?php

namespace Spatie\LaravelPdf;

use Illuminate\Contracts\Cache\Factory as CacheFactory;
use Illuminate\Contracts\Cache\Repository as CacheRepository;
use Illuminate\Contracts\Debug\ExceptionHandler;
use Illuminate\Support\Facades\Blade;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use Spatie\LaravelPdf\Commands\PdfHealthCommand;
use Spatie\LaravelPdf\Drivers\BrowsershotDriver;
use Spatie\LaravelPdf\Drivers\ChromeDriver;
use Spatie\LaravelPdf\Drivers\CloudflareDriver;
use Spatie\LaravelPdf\Drivers\DomPdfDriver;
use Spatie\LaravelPdf\Drivers\FallbackDriver;
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
            ->hasConfigFile('laravel-pdf')
            ->hasCommand(PdfHealthCommand::class);
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

        $this->app->singleton('laravel-pdf.driver.chrome', function () {
            return new ChromeDriver(config('laravel-pdf.chrome', []));
        });

        $this->app->singleton(PdfDriver::class, function () {
            $driverName = config('laravel-pdf.driver', 'browsershot');

            $primary = self::resolveDriverByName($driverName);

            $fallbackNames = config('laravel-pdf.fallback.drivers', []);

            if (! is_array($fallbackNames) || $fallbackNames === []) {
                return $primary;
            }

            return self::buildFallbackDriver([$driverName, ...$fallbackNames]);
        });
    }

    public static function resolveDriverByName(string $driverName): PdfDriver
    {
        return match ($driverName) {
            'browsershot' => app('laravel-pdf.driver.browsershot'),
            'cloudflare' => app('laravel-pdf.driver.cloudflare'),
            'dompdf' => app('laravel-pdf.driver.dompdf'),
            'gotenberg' => app('laravel-pdf.driver.gotenberg'),
            'weasyprint' => app('laravel-pdf.driver.weasyprint'),
            'chrome' => app('laravel-pdf.driver.chrome'),
            default => throw InvalidDriver::unknown($driverName),
        };
    }

    /**
     * @param  array<int, string>  $names
     */
    public static function buildFallbackDriver(array $names): FallbackDriver
    {
        $names = array_values(array_unique($names));

        $drivers = [];
        foreach ($names as $name) {
            $drivers[$name] = self::resolveDriverByName($name);
        }

        $fallbackConfig = config('laravel-pdf.fallback', []);

        $cacheTtl = (int) ($fallbackConfig['health_cache']['ttl'] ?? 0);
        $cacheRepository = $cacheTtl > 0
            ? self::resolveCacheRepository($fallbackConfig['health_cache']['store'] ?? null)
            : null;

        return new FallbackDriver(
            drivers: $drivers,
            exceptionHandler: app(ExceptionHandler::class),
            onlyOnExceptions: $fallbackConfig['only_on_exceptions'] ?? [],
            exceptExceptions: $fallbackConfig['except_exceptions'] ?? [],
            cacheRepository: $cacheRepository,
            healthCacheTtl: $cacheTtl,
            healthCacheKeyPrefix: $fallbackConfig['health_cache']['key_prefix'] ?? 'laravel_pdf_driver_health_',
        );
    }

    protected static function resolveCacheRepository(?string $store): ?CacheRepository
    {
        if (! app()->bound(CacheFactory::class)) {
            return null;
        }

        return app(CacheFactory::class)->store($store);
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

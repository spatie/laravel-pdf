<?php

namespace Spatie\LaravelPdf\Drivers;

use Illuminate\Contracts\Cache\Repository as CacheRepository;
use Illuminate\Contracts\Debug\ExceptionHandler;
use Spatie\LaravelPdf\Exceptions\CouldNotGeneratePdf;
use Spatie\LaravelPdf\PdfOptions;
use Throwable;

class FallbackDriver implements PdfDriver
{
    /**
     * @param  array<string, PdfDriver>  $drivers
     * @param  array<int, class-string<Throwable>>  $onlyOnExceptions
     * @param  array<int, class-string<Throwable>>  $exceptExceptions
     */
    public function __construct(
        protected array $drivers,
        protected ExceptionHandler $exceptionHandler,
        protected array $onlyOnExceptions = [],
        protected array $exceptExceptions = [],
        protected ?CacheRepository $cacheRepository = null,
        protected int $healthCacheTtl = 0,
        protected string $healthCacheKeyPrefix = 'laravel_pdf_driver_health_',
    ) {}

    /**
     * @return array<string, PdfDriver>
     */
    public function getDrivers(): array
    {
        return $this->drivers;
    }

    public function generatePdf(string $html, ?string $headerHtml, ?string $footerHtml, PdfOptions $options): string
    {
        return $this->run(fn (PdfDriver $driver) => $driver->generatePdf($html, $headerHtml, $footerHtml, $options));
    }

    public function savePdf(string $html, ?string $headerHtml, ?string $footerHtml, PdfOptions $options, string $path): void
    {
        $this->run(function (PdfDriver $driver) use ($html, $headerHtml, $footerHtml, $options, $path): bool {
            $driver->savePdf($html, $headerHtml, $footerHtml, $options, $path);

            return true;
        });
    }

    /**
     * @template T
     *
     * @param  callable(PdfDriver): T  $callback
     * @return T
     */
    protected function run(callable $callback): mixed
    {
        $exceptions = [];
        $attemptedNames = [];

        foreach ($this->drivers as $driverName => $driver) {
            if (! $this->isDriverHealthy($driverName)) {
                continue;
            }

            $attemptedNames[] = $driverName;

            try {
                return $callback($driver);
            } catch (Throwable $exception) {
                if (! $this->shouldFallback($exception)) {
                    throw $exception;
                }

                $exceptions[$driverName] = $exception;
                $this->markDriverUnhealthy($driverName);
                $this->exceptionHandler->report($exception);
            }
        }

        if ($attemptedNames === []) {
            throw CouldNotGeneratePdf::allDriversUnhealthy(array_keys($this->drivers));
        }

        throw CouldNotGeneratePdf::allFallbackFailed($attemptedNames, $exceptions);
    }

    protected function shouldFallback(Throwable $exception): bool
    {
        if ($this->onlyOnExceptions !== []) {
            foreach ($this->onlyOnExceptions as $allowed) {
                if ($exception instanceof $allowed) {
                    return true;
                }
            }

            return false;
        }

        foreach ($this->exceptExceptions as $denied) {
            if ($exception instanceof $denied) {
                return false;
            }
        }

        return true;
    }

    protected function isDriverHealthy(string $driverName): bool
    {
        if (! $this->healthCacheEnabled()) {
            return true;
        }

        return ! $this->cacheRepository->has($this->healthCacheKey($driverName));
    }

    protected function markDriverUnhealthy(string $driverName): void
    {
        if (! $this->healthCacheEnabled()) {
            return;
        }

        $this->cacheRepository->put($this->healthCacheKey($driverName), true, $this->healthCacheTtl);
    }

    protected function healthCacheEnabled(): bool
    {
        return $this->cacheRepository !== null && $this->healthCacheTtl > 0;
    }

    protected function healthCacheKey(string $driverName): string
    {
        return $this->healthCacheKeyPrefix.$driverName;
    }
}

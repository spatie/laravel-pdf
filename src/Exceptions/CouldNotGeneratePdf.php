<?php

namespace Spatie\LaravelPdf\Exceptions;

use Exception;
use Throwable;

class CouldNotGeneratePdf extends Exception
{
    /** @var array<int, string> */
    public array $attemptedDrivers = [];

    /** @var array<string, Throwable> */
    public array $driverExceptions = [];

    /**
     * @param  array<int, string>  $drivers
     * @param  array<string, Throwable>  $exceptions
     */
    public static function allFallbackFailed(array $drivers, array $exceptions): self
    {
        $list = implode(', ', $drivers);
        $previous = end($exceptions) ?: null;

        $exception = new self(
            "PDF generation failed after trying all configured drivers: {$list}.",
            previous: $previous ?: null,
        );

        $exception->attemptedDrivers = $drivers;
        $exception->driverExceptions = $exceptions;

        return $exception;
    }

    /**
     * @param  array<int, string>  $drivers
     */
    public static function allDriversUnhealthy(array $drivers): self
    {
        $list = implode(', ', $drivers);

        $exception = new self(
            "All configured PDF drivers are currently marked unhealthy: {$list}."
        );

        $exception->attemptedDrivers = $drivers;

        return $exception;
    }

    public static function browsershotNotInstalled(): self
    {
        return new self(
            'The spatie/browsershot package is required to use the Browsershot driver. '
            .'Install it with: composer require spatie/browsershot'
        );
    }

    public static function missingCloudflareCredentials(): self
    {
        return new self(
            'The Cloudflare driver requires both an API token and account ID. '
            .'Set CLOUDFLARE_API_TOKEN and CLOUDFLARE_ACCOUNT_ID in your .env file.'
        );
    }

    public static function cloudflareApiError(string $body): self
    {
        return new self("Cloudflare PDF generation failed: {$body}");
    }

    public static function dompdfNotInstalled(): self
    {
        return new self(
            'The dompdf/dompdf package is required to use the DOMPDF driver. '
            .'Install it with: composer require dompdf/dompdf'
        );
    }

    public static function weasyPrintPackageNotInstalled(): self
    {
        return new self(
            'The pontedilana/php-weasyprint package is required to use the WeasyPrint driver. '
            .'Install it with: composer require pontedilana/php-weasyprint'
        );
    }

    public static function gotenbergApiError(string $body): self
    {
        return new self("Gotenberg PDF generation failed: {$body}");
    }

    public static function chromeNotInstalled(): self
    {
        return new self(
            'The chrome-php/chrome package is required to use the Chrome PHP driver. '
            .'Install it with: composer require chrome-php/chrome'
        );
    }

    public static function cannotQueueWithBrowsershotClosure(): self
    {
        return new self(
            'Cannot use saveQueued() with withBrowsershot(). '
            .'Closures passed to withBrowsershot() cannot be serialized for the queue.'
        );
    }
}

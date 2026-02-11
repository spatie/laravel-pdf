<?php

namespace Spatie\LaravelPdf\Exceptions;

use Exception;

class CouldNotGeneratePdf extends Exception
{
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

    public static function cannotQueueWithBrowsershotClosure(): self
    {
        return new self(
            'Cannot use saveQueued() with withBrowsershot(). '
            .'Closures passed to withBrowsershot() cannot be serialized for the queue.'
        );
    }
}

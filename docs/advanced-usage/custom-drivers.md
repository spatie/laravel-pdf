---
title: Custom drivers
weight: 5
---

You can create your own PDF generation driver by implementing the `PdfDriver` interface. This allows you to integrate any PDF generation service or library.

## Creating a driver

A driver must implement the `Spatie\LaravelPdf\Drivers\PdfDriver` interface:

```php
namespace App\Pdf\Drivers;

use Spatie\LaravelPdf\Drivers\PdfDriver;
use Spatie\LaravelPdf\PdfOptions;

class WkHtmlToPdfDriver implements PdfDriver
{
    public function __construct(protected array $config = [])
    {
    }

    public function generatePdf(
        string $html,
        ?string $headerHtml,
        ?string $footerHtml,
        PdfOptions $options,
    ): string {
        // Generate and return the PDF content as a string
    }

    public function savePdf(
        string $html,
        ?string $headerHtml,
        ?string $footerHtml,
        PdfOptions $options,
        string $path,
    ): void {
        // Generate the PDF and save it to the given path
    }
}
```

The `PdfOptions` object provides access to the formatting options set on the builder:

- `$options->format` — paper format like `A4`, `Letter`, etc. (or `null`)
- `$options->paperSize` — array with `width`, `height`, and `unit` keys (or `null`)
- `$options->margins` — array with `top`, `right`, `bottom`, `left`, and `unit` keys (or `null`)
- `$options->orientation` — `landscape` or `portrait` (or `null`)

## Registering a driver

Register your driver as a singleton in a service provider:

```php
namespace App\Providers;

use App\Pdf\Drivers\WkHtmlToPdfDriver;
use Illuminate\Support\ServiceProvider;

class PdfServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton('laravel-pdf.driver.wkhtmltopdf', function () {
            return new WkHtmlToPdfDriver(config('laravel-pdf.wkhtmltopdf', []));
        });
    }
}
```

## Using the driver

Once registered, you can use the driver on a per-PDF basis:

```php
use Spatie\LaravelPdf\Facades\Pdf;

Pdf::view('pdfs.invoice', ['invoice' => $invoice])
    ->driver('wkhtmltopdf')
    ->save('invoice.pdf');
```

To make it the default driver, bind it to the `PdfDriver` interface in your service provider:

```php
use App\Pdf\Drivers\WkHtmlToPdfDriver;
use Spatie\LaravelPdf\Drivers\PdfDriver;

$this->app->singleton(PdfDriver::class, function () {
    return new WkHtmlToPdfDriver(config('laravel-pdf.wkhtmltopdf', []));
});
```

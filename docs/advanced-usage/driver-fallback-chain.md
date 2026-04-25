---
title: Driver fallback chain
weight: 5
---

The fallback option lets you configure an ordered chain of drivers. When the primary driver fails, the next driver in the chain is tried automatically — without changing any application code. The chain supports any number of drivers.

This is useful when the primary driver depends on something that can fail outside of your application's control, for example:

- The primary is an external HTTP service (`cloudflare`, `gotenberg`) that may rate-limit, time out, or rotate credentials.
- The primary requires Chrome or Node.js (`browsershot`, `chrome`) and the binary may be missing or misconfigured on some environments (serverless, shared hosting, fresh containers).

The DOMPDF driver is a good last entry in any chain — it runs entirely in PHP and has no external dependencies. The output will not look identical to a Chromium-based driver (see [Using the DOMPDF driver](/docs/laravel-pdf/v2/drivers/using-the-dompdf-driver) for its limitations), but it will produce a usable PDF when nothing else works.

## Basic usage

Set the chain in `config/laravel-pdf.php`:

```php
'driver' => 'gotenberg',

'fallback' => [
    'drivers' => ['chrome', 'dompdf'],
],
```

Or use the `LARAVEL_PDF_FALLBACK_DRIVERS` environment variable, which accepts a comma-separated list:

```env
LARAVEL_PDF_DRIVER=gotenberg
LARAVEL_PDF_FALLBACK_DRIVERS=chrome,dompdf
```

When the variable is empty or unset, no chain is built and the package behaves as before.

## Per-PDF fluent API

You can also configure the chain on a single PDF using the `fallback` method:

```php
use Spatie\LaravelPdf\Facades\Pdf;

Pdf::view('pdfs.invoice', ['invoice' => $invoice])
    ->driver('cloudflare')
    ->fallback('dompdf')
    ->save('invoice.pdf');

// Or pass an ordered list:
Pdf::view('pdfs.invoice', ['invoice' => $invoice])
    ->driver('gotenberg')
    ->fallback(['chrome', 'dompdf'])
    ->save('invoice.pdf');
```

The chain always starts with the driver returned by `driver()` (or the default driver from config), followed by the drivers passed to `fallback()`.

## Filtering which exceptions trigger a fallback

By default, every exception thrown by a driver triggers the fallback. Two options let you narrow this down:

```php
'fallback' => [
    'drivers' => ['chrome', 'dompdf'],

    'only_on_exceptions' => [
        \GuzzleHttp\Exception\ConnectException::class,
    ],

    'except_exceptions' => [
        \Illuminate\View\ViewException::class,
    ],
],
```

- **only_on_exceptions**: allowlist of exception classes that trigger a fallback. When non-empty, only these exceptions cause the next driver to be tried — everything else is re-thrown.
- **except_exceptions**: denylist of exception classes that are always re-thrown as-is.

When both are set, `only_on_exceptions` takes precedence. Leave both empty to fall back on any exception.

## Skipping unhealthy drivers

If a driver is failing, attempting it on every request can be wasteful — for example a Gotenberg container that's down may spend several seconds timing out before the fallback runs. The optional health cache marks a driver as unhealthy after a failure and skips it for a configurable period:

```php
'fallback' => [
    'drivers' => ['gotenberg', 'dompdf'],

    'health_cache' => [
        'ttl'        => env('LARAVEL_PDF_FALLBACK_HEALTH_TTL', 0),
        'key_prefix' => 'laravel_pdf_driver_health_',
        'store'      => env('LARAVEL_PDF_FALLBACK_HEALTH_STORE'),
    ],
],
```

- **ttl**: seconds a failing driver stays marked unhealthy. Set to `0` to disable the cache.
- **key_prefix**: cache key prefix. Override per-tenant if you share a cache store across applications.
- **store**: cache store to use. Defaults to the application's default store when null.

The health cache is disabled by default. Set a TTL like `600` to skip a driver for ten minutes after it fails.

## Inspecting failures

When the entire chain fails, a `CouldNotGeneratePdf` is thrown carrying the full context:

```php
use Spatie\LaravelPdf\Exceptions\CouldNotGeneratePdf;

try {
    Pdf::view('pdfs.invoice', $data)->save('invoice.pdf');
} catch (CouldNotGeneratePdf $e) {
    $e->attemptedDrivers;   // ['gotenberg', 'chrome', 'dompdf']
    $e->driverExceptions;   // ['gotenberg' => ConnectException, ...]
    $e->getPrevious();      // the last driver's exception
}
```

Each individual driver failure is also reported through Laravel's exception handler as it happens, so failures show up in your normal log channels even when a later driver in the chain succeeds.

## Checking driver health from the CLI

The `pdf:health` Artisan command pings every configured driver and reports whether it can generate a small sample PDF:

```bash
php artisan pdf:health
```

```
  INFO  Checking PDF driver health.

  gotenberg (primary) ................................... healthy 362ms
  dompdf (fallback) ..................................... healthy  18ms

  All 2 driver(s) are healthy.
```

The command exits with code `0` when every driver is healthy and `1` otherwise. Use it in a CI job, a readiness probe, or a periodic cron to catch broken drivers early.

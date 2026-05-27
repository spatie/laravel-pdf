---
title: Caching PDFs
weight: 6
---

Rendering a PDF with a Chromium based driver is relatively expensive. When the same document is generated repeatedly, you can cache the rendered result so identical renders are served from the cache instead of being generated again.

## Caching a render

Call `cache()` on the builder. The first render is generated and stored. Every subsequent render with identical input is served from the cache.

```php
use Spatie\LaravelPdf\Facades\Pdf;

Pdf::view('pdf.invoice', ['invoice' => $invoice])
    ->cache()
    ->download('invoice.pdf');
```

Caching applies to every output method, including `save()`, `download()`, `base64()`, returning the PDF as a response, and attaching it to a mail.

## Setting a lifetime

By default a cache entry lives for one day. Pass a lifetime (in seconds) to use a different duration.

```php
Pdf::view('pdf.invoice', ['invoice' => $invoice])
    ->cache(3600)
    ->save('invoice.pdf');
```

## Caching every PDF by default

If you want every PDF to be cached without calling `cache()` each time, enable caching in the config file (or set `LARAVEL_PDF_CACHE_ENABLED=true` in your `.env`).

```php
'cache' => [
    'enabled' => env('LARAVEL_PDF_CACHE_ENABLED', true),
],
```

With caching enabled, calling `cache()` still works to override the lifetime or key for a specific PDF. To skip the cache for a single PDF, call `dontCache()`.

```php
Pdf::view('pdf.receipt', ['receipt' => $receipt])
    ->dontCache()
    ->download('receipt.pdf');
```

## How the cache key is determined

By default the cache key is derived from everything that influences the output: the rendered HTML, the header and footer, all formatting options, the metadata, and the encryption settings. Two renders that differ in any of these get separate cache entries, so you never accidentally serve the wrong PDF.

If you want full control over the key, pass your own as the second argument. This is useful when you can describe a render with a stable identifier, such as an invoice id.

```php
Pdf::view('pdf.invoice', ['invoice' => $invoice])
    ->cache(key: "invoice-{$invoice->id}")
    ->save('invoice.pdf');
```

## Configuring the cache

The caching behaviour is configured in the `cache` section of the `laravel-pdf` config file.

```php
'cache' => [
    'class' => Spatie\LaravelPdf\Caching\DefaultPdfCache::class,
    'enabled' => env('LARAVEL_PDF_CACHE_ENABLED', false),
    'store' => env('LARAVEL_PDF_CACHE_STORE'),
    'prefix' => 'laravel-pdf',
    'ttl' => env('LARAVEL_PDF_CACHE_TTL', 60 * 60 * 24),
],
```

* `enabled`: when `true`, every PDF is cached without calling `cache()`. Defaults to `false`.
* `store`: the cache store to use. Leave it `null` to use your application's default store.
* `prefix`: the prefix prepended to every cache key.
* `ttl`: the default lifetime in seconds. Defaults to one day. Set it to `null` to cache forever.

## Customizing the caching behaviour

If you need to change how PDFs are keyed, stored, or expired, you can replace the entire caching implementation. Create a class that implements the `Spatie\LaravelPdf\Caching\PdfCache` contract.

```php
namespace App\Support;

use Closure;
use Spatie\LaravelPdf\Caching\PdfCache;

class MyPdfCache implements PdfCache
{
    public function remember(string $fingerprint, ?string $key, ?int $ttl, Closure $generate): string
    {
        // Return the cached PDF content, generating and storing it when needed.
    }
}
```

Register it in the `cache.class` key of the config file.

```php
'cache' => [
    'class' => App\Support\MyPdfCache::class,
],
```

## Caching and queued generation

Caching applies to PDFs generated synchronously. PDFs generated with [queued generation](/docs/laravel-pdf/v2/basic-usage/queued-pdf-generation) are not cached.

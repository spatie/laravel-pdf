---
title: Queued PDF generation
weight: 5
---

PDF generation can be slow, especially with the Browsershot or Cloudflare driver. If you don't need the PDF immediately, you can dispatch the generation to a background queue using `saveQueued()`.

## Basic usage

```php
use Spatie\LaravelPdf\Facades\Pdf;

Pdf::view('pdfs.invoice', ['invoice' => $invoice])
    ->format('a4')
    ->saveQueued('invoice.pdf');
```

This will render the HTML from the Blade view eagerly, then dispatch a queued job that generates the PDF and saves it to the given path.

## Callbacks

You can chain `then()` and `catch()` callbacks to react to the job's success or failure:

```php
Pdf::view('pdfs.invoice', ['invoice' => $invoice])
    ->saveQueued('invoice.pdf')
    ->then(fn (string $path, ?string $diskName) => Mail::to($user)->send(new InvoiceMail($path)))
    ->catch(fn (Throwable $e) => Log::error('PDF generation failed', ['error' => $e->getMessage()]));
```

The `then` callback receives the path the PDF was saved to and the disk name (or `null` for local saves). This makes it easy to retrieve the file afterwards:

```php
->then(function (string $path, ?string $diskName) {
    $contents = $diskName
        ? Storage::disk($diskName)->get($path)
        : file_get_contents($path);
})
```

The `catch` callback receives the exception.

## Queue configuration

You can specify the connection and queue name directly:

```php
Pdf::view('pdfs.invoice', $data)
    ->saveQueued('invoice.pdf', connection: 'redis', queue: 'pdfs');
```

Or use chained methods for full control — these are proxied to Laravel's `PendingDispatch`:

```php
Pdf::view('pdfs.invoice', $data)
    ->saveQueued('invoice.pdf')
    ->onQueue('pdfs')
    ->onConnection('redis')
    ->delay(now()->addMinutes(5));
```

## Saving to a disk

When using `disk()`, the queued job will save the PDF to the specified disk:

```php
Pdf::view('pdfs.invoice', $data)
    ->disk('s3')
    ->saveQueued('invoices/invoice.pdf')
    ->then(function (string $path, ?string $diskName) {
        $url = Storage::disk($diskName)->url($path);
        // ...
    });
```

## Customizing the job

You can replace the job class used by `saveQueued()` in your `config/laravel-pdf.php`:

```php
'job' => \App\Jobs\GeneratePdfJob::class,
```

Your custom class should extend the default job:

```php
namespace App\Jobs;

use Spatie\LaravelPdf\Jobs\GeneratePdfJob as BaseJob;

class GeneratePdfJob extends BaseJob
{
    public int $tries = 3;

    public int $timeout = 120;

    public int $backoff = 30;
}
```

This lets you set defaults like retry attempts, timeouts, or a default queue for all queued PDF jobs.

## Limitations

`saveQueued()` cannot be used with `withBrowsershot()`. The closure passed to `withBrowsershot()` may capture objects or state that cannot be reliably serialized for the queue. An exception will be thrown if you try.

## Testing

When using `Pdf::fake()`, you can assert that PDFs were queued:

```php
use Spatie\LaravelPdf\Facades\Pdf;
use Spatie\LaravelPdf\PdfBuilder;

Pdf::fake();

// ... your code that queues a PDF ...

Pdf::assertQueued('invoice.pdf');

// Or use a callable for more detailed assertions:
Pdf::assertQueued(function (PdfBuilder $pdf, string $path) {
    return $path === 'invoice.pdf' && $pdf->contains('Total');
});

// Assert nothing was queued:
Pdf::assertNotQueued();

// Assert a specific path was not queued:
Pdf::assertNotQueued('other.pdf');
```

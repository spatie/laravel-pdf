---
title: Testing PDFs
weight: 7
---

In your test, you can call the `fake()` method on the `Pdf` facade to fake the PDF generation. Because the PDF generation is faked, your tests will run much faster.

```php
// in your test

use Spatie\LaravelPdf\Facades\Pdf;

beforeEach(function () {
    Pdf::fake();
});
```

## assertSaved

You can use the `assertSaved` method to assert that a PDF was saved with specific properties. You should pass it a callable which will received an instance of `Spatie\LaravelPdf\PdfBuilder`. If the callable returns `true`, the assertion will pass.

```php
use Spatie\LaravelPdf\Facades\Pdf;
use Spatie\LaravelPdf\PdfBuilder;

Pdf::assertSaved(function (PdfBuilder $pdf) {
    return $pdf->downloadName === 'invoice.pdf'
        && str_contains($pdf->html, 'Your total for April is $10.00');
});
```

If you want to assert that a PDF was saved to a specific path, you accept the path as a second parameter of the callable.

```php
use Spatie\LaravelPdf\Facades\Pdf;
use Spatie\LaravelPdf\PdfBuilder;

Pdf::assertSaved(function (PdfBuilder $pdf, string $path) {
    return $path === storage_path('invoices/invoice.pdf');
});
```

## assertRespondedWithPdf

The `assertRespondedWithPdf` method can be used to assert that a PDF was generated and returned as a response.

Imagine you have this route:

```php
use Spatie\LaravelPdf\Facades\Pdf;

Route::get('download-invoice', function () {
    return pdf('pdf.invoice')->download('invoice-for-april-2022.pdf');
});
```

In your test for this route you can use the `assertRespondedWithPdf` to make sure that a PDF was generated and returned as a download. You can even make assertions on the content of the PDF.

```php
use Spatie\LaravelPdf\Facades\Pdf;
use Spatie\LaravelPdf\PdfBuilder;

it('can download an invoice', function () {
    $this
        ->get('download-invoice')
        ->assertOk();
        
    Pdf::assertRespondedWithPdf(function (PdfBuilder $pdf) {
        return $pdf->downloadName === 'invoice-for-april-2022.pdf'
            && $pdf->isDownload()
            && str_contains($pdf->html, 'Your total for April is $10.00');
    });
});
```

### Asserting metadata

You can assert that a PDF was generated with specific metadata by inspecting the `metadata` property on the `PdfBuilder` instance.

```php
use Spatie\LaravelPdf\Facades\Pdf;
use Spatie\LaravelPdf\PdfBuilder;

Pdf::assertSaved(function (PdfBuilder $pdf) {
    return $pdf->metadata->title === 'Invoice #123'
        && $pdf->metadata->author === 'Acme Corp';
});
```

## assertBrowsershot

When you customize the underlying Browsershot instance with [`withBrowsershot`](/docs/laravel-pdf/v2/drivers/customizing-browsershot), you can use the `assertBrowsershot` method to verify that customization, without actually starting Chrome.

The closure you pass receives a `Spatie\Browsershot\Browsershot` instance that has your `withBrowsershot` configuration applied to it. If the closure returns `true`, the assertion passes. The assertion runs against every saved PDF and PDF response that was faked.

```php
use Spatie\Browsershot\Browsershot;
use Spatie\LaravelPdf\Facades\Pdf;

Pdf::view('invoice')
    ->withBrowsershot(function (Browsershot $browsershot) {
        $browsershot->setRemoteInstance('127.0.0.1', 9222);
    })
    ->save('invoice.pdf');

Pdf::assertBrowsershot(function (Browsershot $browsershot) {
    return invade($browsershot)->additionalOptions['remoteInstanceUrl'] === 'http://127.0.0.1:9222';
});
```

In the example above, we use [`spatie/invade`](https://github.com/spatie/invade) to read a protected property on the Browsershot instance.

`assertBrowsershot` also honors configuration set on the default builder. A `withBrowsershot` closure registered via `Pdf::default()` (typically in a service provider) is carried over when you call `Pdf::fake()`, so it gets applied to the Browsershot instance passed to your assertion.

## Simple assertion methods

Beside the methods listed above, there are a few simple assertion methods that can be used to assert that a PDF was generated. They are meant to test code that generated a single PDF. The assertions will pass if any of the generated PDFs match the assertion.

If your code generates multiple PDFs, it's better to use the `assertSaved` method.

### assertViewIs

You can use the `assertViewIs` method to assert that a PDF was generated using a specific view.

```php
Pdf::assertViewIs('pdf.invoice');
```

### assertSee

You can use the `assertSee` method to assert that a PDF was generated that contains a given string.

```php
Pdf::assertSee('Your total for April is $10.00');
```

You can pass an array of strings to assert that all of them are present in the PDF.

```php
Pdf::assertSee([
    'Your total for April is $10.00', 
    'Your total for May is $20.00',
]);
```

### assertViewHas

You can use the `assertViewHas` method to assert that a PDF was generated that was passed a specific key in its view data.

```php
Pdf::assertViewHas('invoice');
 ```

As a second parameter you can pass the expected value.

```php
Pdf::assertViewHas('invoice', $invoice);
```

### assertSaved

You can use the `assertSaved` method to assert that a PDF was saved to the specified path.

```php
Pdf::assertSaved(storage_path('invoices/invoice.pdf'));
```

## Queued PDF assertions

### assertQueued

You can use the `assertQueued` method to assert that a PDF was queued for generation. You can pass a string path or a callable.

```php
Pdf::assertQueued('invoice.pdf');
```

With a callable for more detailed assertions:

```php
use Spatie\LaravelPdf\PdfBuilder;

Pdf::assertQueued(function (PdfBuilder $pdf, string $path) {
    return $path === 'invoice.pdf' && $pdf->contains('Total');
});
```

### assertNotQueued

You can use the `assertNotQueued` method to assert that no PDFs were queued, or that a specific path was not queued.

```php
// Assert nothing was queued
Pdf::assertNotQueued();

// Assert a specific path was not queued
Pdf::assertNotQueued('other.pdf');
```

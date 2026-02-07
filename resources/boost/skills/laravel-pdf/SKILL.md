---
name: laravel-pdf
description: Generate PDFs from Blade views or HTML using spatie/laravel-pdf. Covers creating, formatting, saving, downloading, and testing PDFs with the Browsershot or Cloudflare driver.
---

# Laravel PDF

## When to use this skill

Use this skill when the user needs to generate PDFs in a Laravel application using `spatie/laravel-pdf`. This includes creating PDFs from Blade views or HTML, formatting options (margins, orientation, paper size), returning PDFs as downloads or inline responses, saving to disks, testing PDF generation, and configuring drivers.

## Creating PDFs

Create a PDF from a Blade view:

```php
use Spatie\LaravelPdf\Facades\Pdf;

Pdf::view('pdf.invoice', ['invoice' => $invoice])
    ->save('/some/directory/invoice.pdf');
```

Create a PDF from raw HTML:

```php
Pdf::html('<h1>Hello world</h1>')->save('hello.pdf');
```

## Returning PDFs from controllers

Use the `pdf()` helper to return a PDF as a response. By default, it is inlined in the browser:

```php
use function Spatie\LaravelPdf\Support\pdf;

class DownloadInvoiceController
{
    public function __invoke(Invoice $invoice)
    {
        return pdf()
            ->view('pdf.invoice', compact('invoice'))
            ->name('invoice.pdf');
    }
}
```

Force a download:

```php
return pdf()
    ->view('pdf.invoice', compact('invoice'))
    ->name('invoice.pdf')
    ->download();
```

## Formatting

### Paper format

```php
use Spatie\LaravelPdf\Enums\Format;

Pdf::view('pdf.invoice', $data)
    ->format(Format::A4)
    ->save('invoice.pdf');
```

### Orientation

```php
Pdf::view('pdf.invoice', $data)
    ->landscape()
    ->save('invoice.pdf');
```

### Margins

```php
Pdf::view('pdf.invoice', $data)
    ->margins(top: 15, right: 10, bottom: 15, left: 10, unit: 'mm')
    ->save('invoice.pdf');
```

### Custom paper size

```php
Pdf::view('pdf.receipt', $data)
    ->paperSize(57, 500, 'mm')
    ->save('receipt.pdf');
```

### Headers and footers

```php
Pdf::view('pdf.invoice', $data)
    ->headerView('pdf.header', ['company' => $company])
    ->footerView('pdf.footer')
    ->save('invoice.pdf');
```

Or with raw HTML:

```php
Pdf::view('pdf.invoice', $data)
    ->headerHtml('<div>Header</div>')
    ->footerHtml('<div>Footer</div>')
    ->save('invoice.pdf');
```

Inside footer/header views, use `@pageNumber` and `@totalPages` Blade directives. Use `@inlinedImage($path)` to embed images.

### Conditional formatting

```php
Pdf::view('pdf.invoice', $data)
    ->format('a4')
    ->when($invoice->isLandscape(), fn ($pdf) => $pdf->landscape())
    ->save('invoice.pdf');
```

## Saving to disks

```php
Pdf::view('invoice')
    ->disk('s3')
    ->save('invoices/invoice.pdf');
```

## Base64

```php
$base64 = Pdf::view('pdf.invoice', $data)->base64();
```

## Setting defaults

In a service provider:

```php
use Spatie\LaravelPdf\Facades\Pdf;
use Spatie\LaravelPdf\Enums\Format;

Pdf::default()
    ->format(Format::A4)
    ->headerView('pdf.header');
```

## Drivers

The package supports two drivers: `browsershot` (default) and `cloudflare`.

Set the driver via `LARAVEL_PDF_DRIVER` env variable or in `config/laravel-pdf.php`.

### Browsershot driver

Requires `spatie/browsershot` to be installed separately:

```bash
composer require spatie/browsershot
```

Customize the Browsershot instance per PDF:

```php
use Spatie\Browsershot\Browsershot;

Pdf::view('pdf.invoice', $data)
    ->withBrowsershot(function (Browsershot $browsershot) {
        $browsershot->scale(0.5);
    })
    ->save('invoice.pdf');
```

### Cloudflare driver

Uses Cloudflare's Browser Rendering API. No Node.js or Chrome binary needed.

```env
LARAVEL_PDF_DRIVER=cloudflare
CLOUDFLARE_API_TOKEN=your-api-token
CLOUDFLARE_ACCOUNT_ID=your-account-id
```

Switch driver per PDF:

```php
Pdf::view('pdf.invoice', $data)
    ->driver('cloudflare')
    ->save('invoice.pdf');
```

The Cloudflare driver does not support `withBrowsershot()`, `onLambda()`, or PNG output.

## Testing

Fake PDF generation in tests:

```php
use Spatie\LaravelPdf\Facades\Pdf;

beforeEach(function () {
    Pdf::fake();
});
```

Assert a PDF was saved:

```php
Pdf::assertSaved(function (PdfBuilder $pdf, string $path) {
    return $path === storage_path('invoices/invoice.pdf')
        && str_contains($pdf->html, '$10.00');
});
```

Assert a PDF response was returned:

```php
Pdf::assertRespondedWithPdf(function (PdfBuilder $pdf) {
    return $pdf->isDownload()
        && $pdf->downloadName === 'invoice.pdf';
});
```

Simple assertions:

```php
Pdf::assertViewIs('pdf.invoice');
Pdf::assertSee('Your total is $10.00');
Pdf::assertViewHas('invoice', $invoice);
Pdf::assertSaved(storage_path('invoices/invoice.pdf'));
```

## Background color

To render background colors in the PDF, add this CSS:

```html
<style>
    html {
        -webkit-print-color-adjust: exact;
    }
</style>
```

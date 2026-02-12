---
title: Introduction
weight: 1
---

This package provides a simple way to create PDFs in Laravel apps. It uses a driver-based architecture, so you can choose between different PDF generation backends:

- **Browsershot** (default): Uses [Chromium](https://www.chromium.org/chromium-projects/) via [Browsershot](https://spatie.be/docs/browsershot) to generate PDFs from HTML. Requires Node.js and a Chrome/Chromium binary.
- **Gotenberg**: Uses [Gotenberg](https://gotenberg.dev), an open-source Docker-based API with headless Chromium. Great for containerized and microservice environments.
- **Cloudflare**: Uses [Cloudflare's Browser Rendering API](https://developers.cloudflare.com/browser-rendering/) to generate PDFs with a simple HTTP call. No Node.js or Chrome binary needed. This driver was inspired by [a suggestion from Dries Vints](https://x.com/driesvints/status/2016131972477632850).
- **WeasyPrint**: Uses [WeasyPrint](https://doc.courtbouillon.org/weasyprint/stable/) via [pontedilana/php-weasyprint](https://github.com/pontedilana/php-weasyprint) for Python-based PDF generation with excellent CSS Paged Media support. Requires the WeasyPrint binary.
- **DOMPDF**: Uses [dompdf/dompdf](https://github.com/dompdf/dompdf) for pure PHP PDF generation. No external binaries, no Node.js, no Docker — works everywhere PHP runs.

The Browsershot, Gotenberg, and Cloudflare drivers support modern CSS features like grid and flexbox, or even a framework like Tailwind, to create beautiful PDFs. The WeasyPrint driver supports CSS Paged Media, making it a strong choice for documents with repeating headers/footers and page counters. The DOMPDF driver supports CSS 2.1 and some CSS 3 properties, making it ideal for simpler PDFs that don't need advanced layout features.

Here's a quick example:

```php
use Spatie\LaravelPdf\Facades\Pdf;

Pdf::view('pdfs.invoice', ['invoice' => $invoice])
    ->format('a4')
    ->save('invoice.pdf')
```

This will render the Blade view `pdfs.invoice` with the given data and save it as a PDF file.

You can also return the PDF as a response from your controller:

```php
use Spatie\LaravelPdf\Facades\Pdf;

class DownloadInvoiceController
{
    public function __invoke(Invoice $invoice)
    {
        return Pdf::view('pdfs.invoice', ['invoice' => $invoice])
            ->format('a4')
            ->name('your-invoice.pdf');
    }
}
```

You can also queue PDF generation for background processing:

```php
use Spatie\LaravelPdf\Facades\Pdf;

Pdf::view('pdfs.invoice', ['invoice' => $invoice])
    ->format('a4')
    ->saveQueued('invoice.pdf')
    ->then(fn (string $path, ?string $diskName) => Mail::to($user)->send(new InvoiceMail($path)));
```

You can use also test your PDFs:

```php
use Spatie\LaravelPdf\Facades\Pdf;

it('can render an invoice', function () {
    Pdf::fake();

    $invoice = Invoice::factory()->create();

    $this->get(route('download-invoice', $invoice))
        ->assertOk();

    Pdf::assertRespondedWithPdf(function (PdfBuilder $pdf) {
        return $pdf->contains('test');
    });
});
```

## We got badges

[![Latest Version on Packagist](https://img.shields.io/packagist/v/spatie/laravel-pdf.svg?style=flat-square)](https://packagist.org/packages/spatie/laravel-pdf)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/spatie/laravel-pdf/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/spatie/laravel-pdf/actions?query=workflow%3Arun-tests+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/actions/workflow/status/spatie/laravel-pdf/fix-php-code-style-issues.yml?branch=main&label=code%20style&style=flat-square)](https://github.com/spatie/laravel-pdf/actions?query=workflow%3A"Fix+PHP+code+style+issues"+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/spatie/laravel-pdf.svg?style=flat-square)](https://packagist.org/packages/spatie/laravel-pdf)

---
title: Introduction
weight: 1
---

This package provides a simple way to create PDFs in Laravel apps. Under the hood it uses [Chromium](https://www.chromium.org/chromium-projects/) (via [Browsershot](https://spatie.be/docs/browsershot)) to generate PDFs from Blade views. You can use modern CSS features like grid and flexbox, or even a framework like Tailwind, to create beautiful PDFs.

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

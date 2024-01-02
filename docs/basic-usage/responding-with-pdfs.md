---
title: Responding with PDFs
weight: 2
---

In a controller, you can create and return a PDF by using the `pdf()` helper function.

```php
use function Spatie\LaravelPdf\Support\pdf;

class DownloadInvoiceController
{
    public function __invoke(Invoice $invoice)
    {
        return pdf()
            ->view('pdf.invoice', compact('invoice'))
            ->name('invoice-2023-04-10.pdf');
    }
}
```

By default, the PDF will be inlined in the browser. This means that the PDF will be displayed in the browser if the
browser supports it. If the user tries to download the PDF, it will be named "invoice-2023-04-10.pdf". We recommend that
you always name your PDFs.

If you want to force the PDF to be downloaded, you can use the `download()` method.

```php
use function Spatie\LaravelPdf\Support\pdf;

class DownloadInvoiceController
{
    public function __invoke(Invoice $invoice)
    {
        return pdf()
            ->view('pdf.invoice', compact('invoice'))
            ->name('invoice-2023-04-10.pdf')
            ->download();
    }
}
```

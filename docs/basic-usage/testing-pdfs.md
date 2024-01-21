---
title: Testing PDFs
weight: 5
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

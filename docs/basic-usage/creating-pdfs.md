---
title: Creating PDFs
weight: 1
---

This package can be used to create PDFs from HTML. In a Laravel application the easiest way to generate some HTML is to use a Blade view.

Here's an example where we are going to create a PDF from a Blade view.

```php
use Spatie\LaravelPdf\Facades\Pdf;

Pdf::view('pdf.invoice')->save('/some/directory/invoice.pdf');
```

As a second parameter you can pass an array of data that will be made available in the view. You might use that to pass an Eloquent model, such as an invoice, to the view.

```php
use Spatie\LaravelPdf\Facades\Pdf;

Pdf::view('pdf.invoice', ['invoice' => $invoice])
    ->save('/some/directory/invoice.pdf');
```

You can also create a PDF from a string of HTML.

```php
use Spatie\LaravelPdf\Facades\Pdf;

Pdf::html('<h1>Hello world!!</h1>')->save('/some/directory/invoice.pdf');
```

## Using Javascript

The JavaScript in your HTML will be executed when the PDF is created. You could use this to have a JavaScript charting library render a chart.

Here's a simple example. If you have this Blade view...

```blade
<div id="target"></div>

<script>
    document.getElementById('target').innerHTML = 'hello';
</script>
```

... and render it with this code...

```php
use Spatie\LaravelPdf\Facades\Pdf;

Pdf::view('your-view')->save($pathToPdf);
```

... the text `hello` will be visible in the PDF.


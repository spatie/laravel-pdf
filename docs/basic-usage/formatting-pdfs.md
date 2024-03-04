---
title: Formatting PDFs
weight: 3
---

There are various options to customize the output of the PDFs. You can change the page size, the orientation, the margins, and much more!

## Setting a header and footer

You can set a header and footer on every page of the PDF. You can use the `headerView` and `footerView` methods to set the HTML for the header and footer.

```php
use Spatie\LaravelPdf\Facades\Pdf;

Pdf::view('pdf.invoice', ['invoice' => $invoice])
    ->headerView('pdf.invoice.header')
    ->footerView('pdf.invoice.footer')
    ->save('/some/directory/invoice-april-2022.pdf');
```

You can also use the `headerHtml` and `footerHtml` methods to set the HTML for the header and footer.

```php
use Spatie\LaravelPdf\Facades\Pdf;

Pdf::view('pdf.invoice', ['invoice' => $invoice])
    ->headerHtml('<div>My header</div>')
    ->footerHtml('<div>My footer</div>')
    ->save('/some/directory/invoice-april-2022.pdf');
```

The header and footer do not use the CSS set in the main view. In your header and footer HTML, you should add any CSS you need. Here's an example footer view:

```html
<style>
footer {
    font-size: 12px;
}
</style>

<footer>
  This is the footer
</footer>
```

Inside the footer, you can use the following Blade directives:

- `@pageNumber`:  The current page number
- `@totalPages`:  The total number of pages

### Display Images in Headers and Footers

You can add an image using the blade directive `@inlinedImage`

It supports absolute and relative paths

```php
// using relative path
@php $logo = public_path('assets/logo.png'); @endphp
@inlinedImage($logo)

// using absolute path
@inlinedImage('https://some-url/assets/some-logo.png')
```

## Page orientation

By default, all PDFs are created in portrait mode. You can change this by calling the `landscape` method.

```php
use Spatie\LaravelPdf\Facades\Pdf;

Pdf::view('pdf.invoice', ['invoice' => $invoice])
    ->landscape()
    ->save('/some/directory/invoice-april-2022.pdf');
```

Alternatively, you can use the `Orientation` method.

```php
use Spatie\LaravelPdf\Facades\Pdf;
use \Spatie\LaravelPdf\Enums\Orientation;

Pdf::view('pdf.invoice', ['invoice' => $invoice])
    ->orientation(Orientation::Landscape)
    ->save('/some/directory/invoice-april-2022.pdf');
```

## Paper format

By default, all PDFs are created in Letter format. You can change this by calling the `format` method.

```php
use Spatie\LaravelPdf\Facades\Pdf;
use Spatie\LaravelPdf\Enums\Format;

Pdf::view('pdf.invoice', ['invoice' => $invoice])
    ->format(Format::A3) // or you can pass a string like 'a3'
    ->save('/some/directory/invoice-april-2022.pdf');
```

There are the available formats of the `Format` enum:

```php
Letter: 8.5in  x  11in
Legal: 8.5in  x  14in
Tabloid: 11in  x  17in
Ledger: 17in  x  11in
A0: 33.1in  x  46.8in
A1: 23.4in  x  33.1in
A2: 16.54in  x  23.4in
A3: 11.7in  x  16.54in
A4: 8.27in  x  11.7in
A5: 5.83in  x  8.27in
A6: 4.13in  x  5.83in
```

## Paper size

If you don't want to use standardized formats, you can also use the `paperSize` method instead.

```php
use Spatie\LaravelPdf\Facades\Pdf;

Pdf::view('pdf.receipt', ['order' => $order])
    ->paperSize(57, 500, 'mm')
    ->save('/some/directory/receipt-12345.pdf');
```

## Page margins

Margins can be set using the `margins` method. The unit of the margins is millimeters by default.

```php
use Spatie\LaravelPdf\Facades\Pdf;

Pdf::view('pdf.invoice', ['invoice' => $invoice])
    ->margins($top, $right, $bottom, $left)
    ->save('/some/directory/invoice-april-2022.pdf');
```

Optionally you can give a custom unit to the `margins` as the fifth parameter.


```php
use Spatie\LaravelPdf\Facades\Pdf;
use Spatie\LaravelPdf\Enums\Unit;

Pdf::view('pdf.invoice', ['invoice' => $invoice])
    ->margins($top, $right, $bottom, $left, Unit::Pixel)
    ->save('/some/directory/invoice-april-2022.pdf');
```

## Background color

By default, the resulting PDF will not show the background of the html page.

You can set a background using css :

```html
<style>
    html {
        -webkit-print-color-adjust: exact;
    }
</style>
```

Alternatively you can set `print-color-adjust` as `economy` it would generate the pdf document in economy mode.

Or you can set a transparent background using browsershot:

```php
Pdf::view('test')
    ->withBrowsershot(function (Browsershot $browsershot) {
        $browsershot->transparentBackground();
    })
    ->save($this->targetPath);
```

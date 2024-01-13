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

Inside the footer, you can use the following Blade directives:

- `@pageNumber`:  The current page number
- `@totalPages`:  The total number of pages

### Page orientation

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

### Paper format

By default, all PDFs are created in A4 format. You can change this by calling the `paperFormat` method.

```php
use Spatie\LaravelPdf\Facades\Pdf;
use Spatie\LaravelPdf\Enums\Format;

Pdf::view('pdf.invoice', ['invoice' => $invoice])
    ->format(Format::A3) // or you can pass a string like 'a3'
    ->save('/some/directory/invoice-april-2022.pdf');
```

There are the available formats of the `PaperFormat` enum:

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

### Paper size

If you don't want to use standardized formats, you can also use the `paperSize` method instead.

```php
use Spatie\LaravelPdf\Facades\Pdf;

Pdf::view('pdf.receipt', ['order' => $order])
    ->paperSize(57, 500, 'mm')
    ->save('/some/directory/receipt-12345.pdf');
```

### Page margins

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







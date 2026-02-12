---
title: Using the WeasyPrint driver
weight: 5
---

The WeasyPrint driver uses [WeasyPrint](https://doc.courtbouillon.org/weasyprint/stable/) to generate PDFs. WeasyPrint is a Python-based PDF generation tool with excellent [CSS Paged Media](https://www.w3.org/TR/css-page-3/) support, making it a strong choice for documents that need repeating headers and footers, page counters, and fine-grained page layout control.

Unlike the Browsershot and Cloudflare drivers, it does not use a headless browser. Unlike DOMPDF, it supports modern CSS features beyond CSS 2.1. It sits in a sweet spot between the two: no browser required, but with much better CSS support than DOMPDF.

## Getting started

First, install WeasyPrint on your system. Follow the [official installation guide](https://doc.courtbouillon.org/weasyprint/stable/first_steps.html) for your operating system.

Then install the PHP wrapper package:

```bash
composer require pontedilana/php-weasyprint
```

Set the driver in your `.env` file:

```env
LARAVEL_PDF_DRIVER=weasyprint
```

That's it. Your existing PDF code will now use WeasyPrint for generation:

```php
use Spatie\LaravelPdf\Facades\Pdf;

Pdf::view('pdfs.invoice', ['invoice' => $invoice])
    ->format('a4')
    ->save('invoice.pdf');
```

## Supported options

The WeasyPrint driver supports the following PDF options:

- `format()` — Paper format (a4, letter, etc.)
- `paperSize()` — Custom paper dimensions
- `margins()` — Page margins (injected as `@page` CSS)
- `landscape()` / `orientation()` — Page orientation
- `headerHtml()` / `headerView()` — Repeating page headers via CSS Paged Media
- `footerHtml()` / `footerView()` — Repeating page footers via CSS Paged Media

## Configuration

The WeasyPrint driver accepts these configuration options in `config/laravel-pdf.php`:

```php
'weasyprint' => [
    'binary' => env('LARAVEL_PDF_WEASYPRINT_BINARY', 'weasyprint'),
    'timeout' => 10,
],
```

- **binary**: Path to the WeasyPrint binary. Defaults to `weasyprint`, which uses the system `$PATH`.
- **timeout**: Maximum time in seconds for PDF generation. Defaults to `10`.

## Using WeasyPrint for specific PDFs only

If you want to use another driver as your default but switch to WeasyPrint for specific PDFs, use the `driver` method:

```php
use Spatie\LaravelPdf\Facades\Pdf;

Pdf::view('pdfs.invoice', ['invoice' => $invoice])
    ->driver('weasyprint')
    ->format('a4')
    ->save('invoice.pdf');
```

## How headers and footers work

WeasyPrint supports repeating headers and footers on every page using CSS Paged Media. When you use `headerHtml()` or `footerHtml()`, the driver wraps your content in elements that use CSS `position: running()` and places them in `@page` margin boxes. This means headers and footers will repeat on every page automatically.

Page numbers and total pages are also supported via the `@pageNumber` and `@totalPages` Blade directives, which use CSS counters (`counter(page)` and `counter(pages)`).

## Limitations

- **CSS support**: WeasyPrint supports CSS 2.1 and large parts of CSS 3, including Paged Media. However, it does not support JavaScript or CSS features that rely on a full browser engine (e.g., some flexbox edge cases).
- **JavaScript**: WeasyPrint does not execute JavaScript. All content must be static HTML/CSS.
- **External binary**: Requires the WeasyPrint binary (Python) to be installed on your system.
- The `withBrowsershot()` and `onLambda()` methods have no effect when using the WeasyPrint driver.

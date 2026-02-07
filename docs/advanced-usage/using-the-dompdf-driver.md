---
title: Using the DOMPDF driver
weight: 4
---

The DOMPDF driver uses [dompdf/dompdf](https://github.com/dompdf/dompdf) to generate PDFs entirely in PHP. Unlike the Browsershot and Cloudflare drivers, it does not require Node.js, Chrome, Docker, or any external service. It works everywhere PHP runs.

This makes it a great choice for simple PDFs, shared hosting environments, or situations where you can't install external binaries.

**Note:** DOMPDF does not support all CSS. It handles CSS 2.1 and some CSS 3 properties, but modern layout features like flexbox and grid are not supported. Use tables or floats for layout. If you need full CSS support, use the Browsershot or Cloudflare driver instead.

## Getting started

Install the dompdf package:

```bash
composer require dompdf/dompdf
```

Then set the driver in your `.env` file:

```env
LARAVEL_PDF_DRIVER=dompdf
```

That's it. Your existing PDF code will now use DOMPDF for generation:

```php
use Spatie\LaravelPdf\Facades\Pdf;

Pdf::view('pdfs.invoice', ['invoice' => $invoice])
    ->format('a4')
    ->save('invoice.pdf');
```

## Supported options

The DOMPDF driver supports the following PDF options:

- `format()` — Paper format (a4, letter, legal, etc.)
- `paperSize()` — Custom paper dimensions
- `margins()` — Page margins (injected as `@page` CSS)
- `landscape()` / `orientation()` — Page orientation
- `headerHtml()` / `headerView()` — Prepended to the body content
- `footerHtml()` / `footerView()` — Appended to the body content

## Configuration

The DOMPDF driver accepts these configuration options in `config/laravel-pdf.php`:

```php
'dompdf' => [
    'is_remote_enabled' => env('LARAVEL_PDF_DOMPDF_REMOTE_ENABLED', false),
    'chroot' => env('LARAVEL_PDF_DOMPDF_CHROOT'),
],
```

- **is_remote_enabled**: Set to `true` if your HTML references external images or CSS files via URLs.
- **chroot**: The base path for local file access. Defaults to DOMPDF's built-in setting.

## Using DOMPDF for specific PDFs only

If you want to use another driver as your default but switch to DOMPDF for specific PDFs, use the `driver` method:

```php
use Spatie\LaravelPdf\Facades\Pdf;

Pdf::view('pdfs.invoice', ['invoice' => $invoice])
    ->driver('dompdf')
    ->format('a4')
    ->save('invoice.pdf');
```

## How margins work

DOMPDF does not have a dedicated margin API. Instead, this driver injects an `@page { margin: ... }` CSS rule into your HTML. This means margins are applied via CSS, which works well for most use cases.

## How headers and footers work

Unlike the Chromium-based drivers, DOMPDF does not support repeating HTML headers and footers on every page. Instead, the header HTML is prepended and the footer HTML is appended to the body content. They will appear at the top and bottom of the first page.

If you need repeating headers and footers on every page, consider using the Browsershot or Cloudflare driver instead.

## Limitations

- **CSS support**: DOMPDF supports CSS 2.1 and some CSS 3 properties, but does not support modern layout features like flexbox or grid. Use tables or floats for layout.
- **JavaScript**: DOMPDF does not execute JavaScript. All content must be static HTML/CSS.
- **Headers/footers**: Not repeated on every page — they are prepended/appended to the body.
- **External resources**: Fetching external images and CSS is disabled by default. Set `is_remote_enabled` to `true` in the config if needed.
- The `withBrowsershot()` and `onLambda()` methods have no effect when using the DOMPDF driver.

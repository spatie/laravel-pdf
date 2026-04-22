---
title: Using the Chrome driver
weight: 5
---

The Chrome driver uses [chrome-php/chrome](https://github.com/chrome-php/chrome) to generate PDFs directly from PHP. It is a good fit when you want a Chromium-based driver without bringing in Node.js or Puppeteer.

## Getting started

1. Install the Chrome package:

```bash
composer require chrome-php/chrome
```

2. Make sure Chrome or Chromium is installed on the machine that generates the PDFs.

The upstream library requires a Chrome/Chromium 65+ executable. It can auto-discover the browser in common locations, but you can also configure the binary path explicitly.

3. Set the driver in your `.env` file:

```env
LARAVEL_PDF_DRIVER=chrome
```

If Chrome cannot be auto-discovered, also set the browser path:

```env
LARAVEL_PDF_CHROME_BINARY=/usr/bin/google-chrome-stable
```

That's it. Your existing PDF code will now use the Chrome driver:

```php
use Spatie\LaravelPdf\Facades\Pdf;

Pdf::view('pdfs.invoice', ['invoice' => $invoice])
    ->format('a4')
    ->save('invoice.pdf');
```

## Supported options

The Chrome driver supports the following PDF options:

- `format()` — Paper format (a4, letter, legal, etc.)
- `paperSize()` — Custom paper dimensions
- `margins()` — Page margins
- `landscape()` / `orientation()` — Page orientation
- `scale()` — Page rendering scale
- `pageRanges()` — Specific pages to include
- `headerView()` / `headerHtml()` — Page headers
- `footerView()` / `footerHtml()` — Page footers

## Configuration

The Chrome driver accepts these configuration options in `config/laravel-pdf.php`:

```php
'chrome' => [
    'chrome_binary' => env('LARAVEL_PDF_CHROME_BINARY'),
    'no_sandbox' => env('LARAVEL_PDF_CHROME_NO_SANDBOX', false),
    'startup_timeout' => env('LARAVEL_PDF_CHROME_STARTUP_TIMEOUT', 30),
    'timeout' => env('LARAVEL_PDF_CHROME_TIMEOUT', 30000),
    'user_data_dir' => env('LARAVEL_PDF_CHROME_USER_DATA_DIR'),
    'custom_flags' => [],
    'env_variables' => [],
],
```

- **chrome_binary**: The path to the Chrome or Chromium executable. If omitted, the driver will try to auto-discover it.
- **no_sandbox**: Disables Chrome's sandbox. This is sometimes needed in Docker or restricted server environments.
- **startup_timeout**: Maximum time in seconds to wait for Chrome to start.
- **timeout**: Maximum time in milliseconds to wait when setting the page HTML.
- **user_data_dir**: Custom Chrome profile directory.
- **custom_flags**: Additional Chrome command-line flags.
- **env_variables**: Environment variables passed to the Chrome process.

## Using Chrome for specific PDFs only

If you want to use another driver as your default but switch to Chrome for specific PDFs, use the `driver` method:

```php
use Spatie\LaravelPdf\Facades\Pdf;

Pdf::view('pdfs.invoice', ['invoice' => $invoice])
    ->driver('chrome')
    ->format('a4')
    ->save('invoice.pdf');
```

## Limitations

- The Chrome driver requires both the `chrome-php/chrome` package and a local Chrome or Chromium executable. It does not download or bundle a browser for you.
- In Docker or other locked-down environments, you may need to enable `no_sandbox`.
- This driver only documents and exposes the options supported by Laravel PDF. If you need deeper Chromium customization, use the Browsershot driver instead.
- The `tagged()`, `withBrowsershot()` and `onLambda()` methods have no effect when using the Chrome driver.

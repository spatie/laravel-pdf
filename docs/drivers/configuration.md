---
title: Configuration
weight: 1
---

Laravel PDF supports configuration-based customization, allowing you to set default options that apply to all PDF generation in your application.

## Publishing the Configuration File

To publish the configuration file, run:

```bash
php artisan vendor:publish --tag=pdf-config
```

This will create a `config/laravel-pdf.php` file in your application.

## Selecting a Driver

The `driver` option determines which PDF generation backend to use:

```php
'driver' => env('LARAVEL_PDF_DRIVER', 'browsershot'),
```

Supported values: `browsershot`, `cloudflare`, `dompdf`, `gotenberg`, `weasyprint`.

## Browsershot Configuration

Configure paths to Node.js, npm, Chrome, and other binaries used by the Browsershot driver:

```php
'browsershot' => [
    'node_binary' => env('LARAVEL_PDF_NODE_BINARY'),
    'npm_binary' => env('LARAVEL_PDF_NPM_BINARY'),
    'include_path' => env('LARAVEL_PDF_INCLUDE_PATH'),
    'chrome_path' => env('LARAVEL_PDF_CHROME_PATH'),
    'node_modules_path' => env('LARAVEL_PDF_NODE_MODULES_PATH'),
    'bin_path' => env('LARAVEL_PDF_BIN_PATH'),
    'temp_path' => env('LARAVEL_PDF_TEMP_PATH'),
    'write_options_to_file' => env('LARAVEL_PDF_WRITE_OPTIONS_TO_FILE', false),
    'no_sandbox' => env('LARAVEL_PDF_NO_SANDBOX', false),
],
```

## Cloudflare Configuration

Configure the Cloudflare Browser Rendering API credentials:

```php
'cloudflare' => [
    'api_token' => env('CLOUDFLARE_API_TOKEN'),
    'account_id' => env('CLOUDFLARE_ACCOUNT_ID'),
],
```

## Gotenberg Configuration

Configure the Gotenberg API URL:

```php
'gotenberg' => [
    'url' => env('GOTENBERG_URL', 'http://localhost:3000'),
],
```

## WeasyPrint Configuration

Configure the WeasyPrint driver options:

```php
'weasyprint' => [
    'binary' => env('LARAVEL_PDF_WEASYPRINT_BINARY', 'weasyprint'),
    'timeout' => 10,
],
```

- **binary**: Path to the WeasyPrint binary. Defaults to `weasyprint` (found via `$PATH`).
- **timeout**: Maximum time in seconds for PDF generation. Defaults to `10`.

## DOMPDF Configuration

Configure the DOMPDF driver options:

```php
'dompdf' => [
    'is_remote_enabled' => env('LARAVEL_PDF_DOMPDF_REMOTE_ENABLED', false),
    'chroot' => env('LARAVEL_PDF_DOMPDF_CHROOT'),
],
```

- **is_remote_enabled**: Set to `true` to allow DOMPDF to fetch external resources (images, CSS) via URLs.
- **chroot**: The base path for local file access. Defaults to DOMPDF's built-in setting.

## Environment Variables

You can use environment variables to configure PDF generation:

```env
# Driver selection
LARAVEL_PDF_DRIVER=browsershot

# Browsershot settings
LARAVEL_PDF_NODE_BINARY=/usr/local/bin/node
LARAVEL_PDF_NPM_BINARY=/usr/local/bin/npm
LARAVEL_PDF_INCLUDE_PATH=/usr/local/bin
LARAVEL_PDF_CHROME_PATH=/usr/bin/google-chrome-stable
LARAVEL_PDF_NODE_MODULES_PATH=/path/to/node_modules
LARAVEL_PDF_BIN_PATH=/usr/local/bin
LARAVEL_PDF_TEMP_PATH=/tmp
LARAVEL_PDF_WRITE_OPTIONS_TO_FILE=true
LARAVEL_PDF_NO_SANDBOX=true

# Cloudflare settings
CLOUDFLARE_API_TOKEN=your-api-token
CLOUDFLARE_ACCOUNT_ID=your-account-id

# Gotenberg settings
GOTENBERG_URL=http://localhost:3000

# WeasyPrint settings
LARAVEL_PDF_WEASYPRINT_BINARY=/usr/local/bin/weasyprint

# DOMPDF settings
LARAVEL_PDF_DOMPDF_REMOTE_ENABLED=false
LARAVEL_PDF_DOMPDF_CHROOT=/path/to/chroot

```

## Runtime Driver Switching

You can switch drivers on a per-PDF basis using the `driver` method:

```php
use Spatie\LaravelPdf\Facades\Pdf;

// Use the Cloudflare driver for this specific PDF
Pdf::view('invoice', $data)
    ->driver('cloudflare')
    ->save('invoice.pdf');

// Use the default driver from config
Pdf::view('invoice', $data)
    ->save('invoice.pdf');
```

## Overriding Browsershot Configuration

When using the Browsershot driver, configuration defaults can be overridden on a per-PDF basis using the `withBrowsershot()` method:

```php
use Spatie\LaravelPdf\Facades\Pdf;
use Spatie\Browsershot\Browsershot;

// This PDF will use the configuration defaults plus the scale override
Pdf::view('invoice', ['invoice' => $invoice])
    ->withBrowsershot(function (Browsershot $browsershot) {
        $browsershot->scale(0.8);
    })
    ->save('invoice.pdf');
```

The `withBrowsershot()` closure runs after configuration defaults are applied, allowing you to modify or override any setting.

---
title: Installation & setup
weight: 4
---

You can install the package via composer:

```bash
composer require spatie/laravel-pdf
```

## Choosing a driver

This package supports multiple PDF generation drivers. You can set the driver via the `LARAVEL_PDF_DRIVER` environment variable, or in the config file.

### Browsershot driver (default)

The Browsershot driver requires the `spatie/browsershot` package:

```bash
composer require spatie/browsershot
```

You'll also need to install the required dependencies for Browsershot to work. You can find the instructions [here](https://spatie.be/docs/browsershot/v4/requirements).

### Cloudflare driver

The Cloudflare driver uses [Cloudflare's Browser Rendering API](https://developers.cloudflare.com/browser-rendering/) to generate PDFs. It does not require Node.js or a Chrome binary, making it a great choice for cloud-hosted Laravel apps.

To get started with Cloudflare:

1. Make sure you have a [Cloudflare account](https://dash.cloudflare.com/sign-up)
2. In the Cloudflare dashboard, go to **Manage account > Account API tokens** in the sidebar
3. Click **Create Token** and create a token with the **Account.Browser Rendering** permission
4. Your Account ID can be found in the address bar of the Cloudflare dashboard URL
5. Add the following to your `.env` file:

```env
LARAVEL_PDF_DRIVER=cloudflare
CLOUDFLARE_API_TOKEN=your-api-token
CLOUDFLARE_ACCOUNT_ID=your-account-id
```

That's it. No other dependencies are required since the Cloudflare driver uses Laravel's built-in HTTP client.

### WeasyPrint driver

The WeasyPrint driver uses [WeasyPrint](https://doc.courtbouillon.org/weasyprint/stable/), a Python-based PDF generation tool with excellent CSS Paged Media support including repeating headers/footers and page counters.

First, install the WeasyPrint binary on your system. Follow the [installation guide](https://doc.courtbouillon.org/weasyprint/stable/first_steps.html) for your OS.

Then install the PHP wrapper:

```bash
composer require pontedilana/php-weasyprint
```

Set the driver in your `.env` file:

```env
LARAVEL_PDF_DRIVER=weasyprint
```

See [Using the WeasyPrint driver](/docs/laravel-pdf/v2/drivers/using-the-weasyprint-driver) for more details on configuration and supported options.

### DOMPDF driver

The DOMPDF driver requires no external binaries, no Node.js, and no Docker. It works everywhere PHP runs.

```bash
composer require dompdf/dompdf
```

Then set the driver in your `.env` file:

```env
LARAVEL_PDF_DRIVER=dompdf
```

Note that DOMPDF has more limited CSS support than the Chromium-based drivers. See [Using the DOMPDF driver](/docs/laravel-pdf/v2/drivers/using-the-dompdf-driver) for details.

## Laravel Boost

This package ships with a [Laravel Boost](https://laravel.com/docs/12.x/boost) skill. After installing the package, run `php artisan boost:install` to register the skill. This will help AI agents in your project generate correct PDF code.

## Publishing the config file

Optionally, you can publish the config file with:

```bash
php artisan vendor:publish --tag=pdf-config
```

This is the content of the published config file:

```php
return [
    'driver' => env('LARAVEL_PDF_DRIVER', 'browsershot'),

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

    'cloudflare' => [
        'api_token' => env('CLOUDFLARE_API_TOKEN'),
        'account_id' => env('CLOUDFLARE_ACCOUNT_ID'),
    ],

    'dompdf' => [
        'is_remote_enabled' => env('LARAVEL_PDF_DOMPDF_REMOTE_ENABLED', false),
        'chroot' => env('LARAVEL_PDF_DOMPDF_CHROOT'),
    ],

    'weasyprint' => [
        'binary' => env('LARAVEL_PDF_WEASYPRINT_BINARY', 'weasyprint'),
        'timeout' => 10,
    ],

];
```

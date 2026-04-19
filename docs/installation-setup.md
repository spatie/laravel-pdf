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

### Gotenberg driver

The Gotenberg driver uses [Gotenberg](https://gotenberg.dev), an open-source Docker-based API with headless Chromium.

You will need a running Gotenberg instance (started with `docker run --rm -p 3000:3000 gotenberg/gotenberg:8`, or similar; see the [installation guide](https://gotenberg.dev/docs/getting-started/installation) for additional setup instructions).

Add the following to your `.env` file:

```env
LARAVEL_PDF_DRIVER=gotenberg
GOTENBERG_URL=http://your-host:your-port
# If you set up authentication, add these lines too:
GOTENBERG_USERNAME=username
GOTENBERG_PASSWORD=password
```

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

### Chrome driver

The Chrome driver uses [chrome-php/chrome](https://github.com/chrome-php/chrome) to talk directly to a local Chrome or Chromium binary from PHP.

```bash
composer require chrome-php/chrome
```

Then set the driver in your `.env` file:

```env
LARAVEL_PDF_DRIVER=chrome
```

You'll also need to install the Chrome/Chromium binary to work. You can find the instructions [here](https://www.chromium.org/getting-involved/download-chromium/).

See [Using the Chrome driver](/docs/laravel-pdf/v2/drivers/using-the-chrome-driver) for more details on configuration, supported options, and limitations.

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

    'chrome' => [
        'chrome_binary' => env('LARAVEL_PDF_CHROME_BINARY', env('LARAVEL_PDF_CHROME_PATH')),
        'no_sandbox' => env('LARAVEL_PDF_CHROME_NO_SANDBOX', false),
        'startup_timeout' => env('LARAVEL_PDF_CHROME_STARTUP_TIMEOUT', 30),
        'timeout' => env('LARAVEL_PDF_CHROME_TIMEOUT', 30000),
        'user_data_dir' => env('LARAVEL_PDF_CHROME_USER_DATA_DIR'),
        'custom_flags' => [],
        'env_variables' => [],
    ],
];
```

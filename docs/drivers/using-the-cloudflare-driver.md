---
title: Using the Cloudflare driver
weight: 3
---

The Cloudflare driver uses [Cloudflare's Browser Rendering API](https://developers.cloudflare.com/browser-rendering/) to generate PDFs. Unlike the Browsershot driver, it does not require Node.js or a Chrome binary on your server. Instead, it makes a simple HTTP call to Cloudflare's API, which renders your HTML and returns a PDF.

This approach was inspired by this tweet by [Dries Vints](https://x.com/driesvints/status/2016131972477632850).

## Getting started

1. Make sure you have a [Cloudflare account](https://dash.cloudflare.com/sign-up)
2. In the Cloudflare dashboard, go to **Manage account > Account API tokens** in the sidebar
3. Click **Create Token** and create a token with the **Account.Browser Rendering** permission
4. Your Account ID can be found in the address bar of the Cloudflare dashboard URL

![Cloudflare API tokens page](/docs/laravel-pdf/v2/images/cloudflare-api-tokens.jpg)

5. Add the following to your `.env` file:

```env
LARAVEL_PDF_DRIVER=cloudflare
CLOUDFLARE_API_TOKEN=your-api-token
CLOUDFLARE_ACCOUNT_ID=your-account-id
```

That's it. Your existing PDF code will now use Cloudflare for generation:

```php
use Spatie\LaravelPdf\Facades\Pdf;

// This will use Cloudflare — no code changes needed
Pdf::view('pdfs.invoice', ['invoice' => $invoice])
    ->format('a4')
    ->save('invoice.pdf');
```

## Supported options

The Cloudflare driver supports the following PDF options:

- `format()` — Paper format (a4, letter, etc.)
- `paperSize()` — Custom paper dimensions
- `margins()` — Page margins
- `landscape()` / `orientation()` — Page orientation
- `headerView()` / `headerHtml()` — Page headers
- `footerView()` / `footerHtml()` — Page footers

## Using Cloudflare for specific PDFs only

If you want to use Browsershot as your default driver but switch to Cloudflare for specific PDFs, you can use the `driver` method:

```php
use Spatie\LaravelPdf\Facades\Pdf;

Pdf::view('pdfs.invoice', ['invoice' => $invoice])
    ->driver('cloudflare')
    ->format('a4')
    ->save('invoice.pdf');
```

Make sure you have the Cloudflare credentials configured in your `config/laravel-pdf.php` or `.env`, even if Cloudflare is not the default driver.

## Limits

The free Cloudflare Workers plan is limited to 6 REST API requests per minute and 10 minutes of Browser Rendering usage per day. The paid Workers plan offers significantly higher limits. Check the [Cloudflare Browser Rendering limits page](https://developers.cloudflare.com/browser-rendering/platform/limits/) for the latest details.

## Limitations

- The Cloudflare driver only supports PDF output. Saving as PNG (screenshots) is not supported.
- The `withBrowsershot()` and `onLambda()` methods have no effect when using the Cloudflare driver.
- JavaScript execution depends on Cloudflare's Browser Rendering API behavior — complex scripts may behave differently than in a local Chromium instance.

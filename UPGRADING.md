# Upgrading from v1 to v2

v2 introduces a driver-based architecture, allowing you to choose between different PDF generation backends. This guide covers all breaking changes and the steps needed to upgrade.

## Install Browsershot explicitly

In v1, `spatie/browsershot` was installed automatically. In v2, it has been moved to a suggested dependency. If you use the Browsershot driver (the default), you must require it explicitly:

```bash
composer require spatie/browsershot
```

If you skip this step, you'll get a `CouldNotGeneratePdf` exception when generating PDFs.

## Update the config file

If you published the config file, add the new `driver` key and the `cloudflare` and `dompdf` sections:

```php
return [
    'driver' => env('LARAVEL_PDF_DRIVER', 'browsershot'),

    'browsershot' => [
        // ... your existing browsershot config
    ],

    'cloudflare' => [
        'api_token' => env('CLOUDFLARE_API_TOKEN'),
        'account_id' => env('CLOUDFLARE_ACCOUNT_ID'),
    ],

    'dompdf' => [
        'is_remote_enabled' => env('LARAVEL_PDF_DOMPDF_REMOTE_ENABLED', false),
        'chroot' => env('LARAVEL_PDF_DOMPDF_CHROOT'),
    ],
];
```

If you haven't published the config file, no action is needed.

## Replace `getBrowsershot()` calls

The `getBrowsershot()` method has been removed from `PdfBuilder`. If you were using it to customize the Browsershot instance, use the `withBrowsershot()` method instead:

```php
// Before (v1)
$browsershot = Pdf::view('invoice', $data)->getBrowsershot();
$browsershot->scale(0.5);
$browsershot->save('invoice.pdf');

// After (v2)
Pdf::view('invoice', $data)
    ->withBrowsershot(function (Browsershot $browsershot) {
        $browsershot->scale(0.5);
    })
    ->save('invoice.pdf');
```

## Update exception handling for image loading

The `@inlinedImage` Blade directive now throws `Spatie\LaravelPdf\Exceptions\CouldNotLoadImage` instead of `Illuminate\View\ViewException`. If you catch image loading exceptions, update your catch blocks:

```php
// Before (v1)
try {
    // render PDF with @inlinedImage
} catch (\Illuminate\View\ViewException $e) {
    // handle image error
}

// After (v2)
try {
    // render PDF with @inlinedImage
} catch (\Spatie\LaravelPdf\Exceptions\CouldNotLoadImage $e) {
    // handle image error
}
```

## Update tests that inspect Browsershot internals

If your tests used `getBrowsershot()` with `invade()` to inspect Browsershot configuration, you'll need to create a `BrowsershotDriver` instance directly:

```php
// Before (v1)
$browsershot = Pdf::view('test')->getBrowsershot();
expect(invade($browsershot)->nodeBinary)->toBe('/test/node');

// After (v2)
use Spatie\LaravelPdf\Drivers\BrowsershotDriver;
use Spatie\LaravelPdf\PdfOptions;

$driver = new BrowsershotDriver(config('laravel-pdf.browsershot'));
$browsershot = invade($driver)->buildBrowsershot('test', null, null, new PdfOptions);
expect(invade($browsershot)->nodeBinary)->toBe('/test/node');
```

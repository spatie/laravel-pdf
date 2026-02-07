---
title: Customizing Browsershot
weight: 2
---

When using the Browsershot driver, you can customize the underlying [Browsershot](https://spatie.be/docs/browsershot) instance. This is only applicable when using the `browsershot` driver.

## Configuration-Based Customization

For settings that apply to all PDFs in your application, use the [configuration file](/docs/advanced-usage/configuration) to set defaults. This is especially useful for binary paths, Chrome arguments, and language settings.

## Per-PDF Customization

You can customize the Browsershot instance for individual PDFs by calling the `withBrowsershot` method. This method accepts a closure that receives the Browsershot instance as its only argument.

Here's an example of how you can call Browsershot's `scale` method:

```php
use Spatie\LaravelPdf\Facades\Pdf;
use Spatie\Browsershot\Browsershot;

Pdf::view('test')
    ->withBrowsershot(function (Browsershot $browsershot) {
        $browsershot->scale(0.5);
    })
    ->save($this->targetPath);
```

The `withBrowsershot()` closure runs after configuration defaults are applied, allowing you to override or extend the default settings on a per-PDF basis.

## Advanced Browsershot Configuration

You can also use the `withBrowsershot` method to set options on the Browsershot instance. For example, you can disable web security and allow file access from files.

These flags are commonly needed when your PDF templates reference local assets (CSS, images, fonts) or when you need to bypass CORS restrictions during PDF generation. Without these flags, Chrome might block access to local resources, causing missing styles or images in your PDFs.

The two Chrome flags being set are:

- `--disable-web-security`: Disables Chrome's same-origin policy and other web security features
- `--allow-file-access-from-files`: Allows local files to access other local files (normally blocked for security)

You can set these as defaults for all PDFs:

```php
// typically, in a service provider

Pdf::default()->withBrowsershot(function (Browsershot $browsershot) {
    $browsershot->setOption('args', [
        '--disable-web-security',
        '--allow-file-access-from-files',
    ]);
});
```

---
title: Customizing Browsershot
weight: 2
---

Under the hood, Laravel PDF uses [Browsershot](https://spatie.be/docs/browsershot) to generate the PDFs. While Laravel PDF provides a simple interface to generate PDFs, you can still use Browsershot directly to customize the PDFs.

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

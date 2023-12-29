---
title: Customizing Browsershot
weight: 2
---

Under the hood, Laravel PDF uses [Browsershot](https://spatie.be/docs/browsershot) to generate the PDFs. While Laravel PDF provides a simple interface to generate PDFs, you can still use Browsershot directly to customize the PDFs.


You can customize the Browsershot instance by calling the `withBrowsershot` method. This method accepts a closure that receives the Browsershot instance as its only argument. You can use this instance to customize the PDFs.

Here's an example of how you can call Browsershot's `scale` method.

```php
use Spatie\LaravelPdf\Facades\Pdf;

Pdf::view('test')
    ->withBrowsershot(function (Browsershot $browsershot) {
        $browsershot->scale(0.5);
    })
    ->save($this->targetPath);
```

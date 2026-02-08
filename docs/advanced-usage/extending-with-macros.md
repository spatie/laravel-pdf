---
title: Extending with Macros
weight: 4
---

The `PdfBuilder` class is macroable, which means you can add custom methods to it using Laravel's macro functionality. This allows you to extend the PDF builder with your own custom methods.

## Adding macros

You can add macros to the `PdfBuilder` class in your service provider's `boot` method:

```php
use Spatie\LaravelPdf\PdfBuilder;

class AppServiceProvider extends ServiceProvider
{
    public function boot()
    {
        PdfBuilder::macro('withCustomHeader', function (string $title) {
            $this->headerHtml = "<h1>{$title}</h1>";

            return $this;
        });
    }
}
```

## Using macros

Once you've added a macro, you can use it just like any other method on the `PdfBuilder` class:

```php
use Spatie\LaravelPdf\Facades\Pdf;

Pdf::view('invoice')
    ->withCustomHeader('Invoice #123')
    ->save('invoice.pdf');
```

Macros are particularly useful for:

-   Adding organization-specific formatting options
-   Creating reusable PDF components
-   Standardizing your PDF outputs across your application

---
title: Setting defaults
weight: 6
---

You can set the default options for every PDF, by using the `default` method on the `Pdf` facade.

Typically, you would do this in the `boot` method of a service provider.

```php
use Spatie\LaravelPdf\Facades\Pdf;
use Spatie\LaravelPdf\Enums\Format;

// in a service provider

Pdf::default()
    ->headerView('pdf.header')
    ->format(Format::A3);
```

With this code, every PDF generated in your app will have the `pdf.header` view as header and will be rendered in A3 format.

Of course, you can still override these defaults when generating a PDF.

```php
// this PDF will use the defaults: it will be rendered in A3 format

Pdf::html('<h1>Hello world</h1>')
    ->save('my-a3-pdf.pdf')

// here we override the default: this PDF will be rendered in A4 format

Pdf::html('<h1>Hello world</h1>')
   ->format(Format::A4)
   ->save('my-a4-pdf.pdf')
```

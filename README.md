# Create PDFs in Laravel apps

[![Latest Version on Packagist](https://img.shields.io/packagist/v/spatie/laravel-pdf.svg?style=flat-square)](https://packagist.org/packages/spatie/laravel-pdf)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/spatie/laravel-pdf/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/spatie/laravel-pdf/actions?query=workflow%3Arun-tests+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/actions/workflow/status/spatie/laravel-pdf/fix-php-code-style-issues.yml?branch=main&label=code%20style&style=flat-square)](https://github.com/spatie/laravel-pdf/actions?query=workflow%3A"Fix+PHP+code+style+issues"+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/spatie/laravel-pdf.svg?style=flat-square)](https://packagist.org/packages/spatie/laravel-pdf)

This package provides a simple way to create PDFs in Laravel apps. Under the hood it uses [Chromium](https://www.chromium.org/chromium-projects/) to generate PDFs from Blade views. You can use modern CSS features like grid and flexbox to create beautiful PDFs.

Here's a quick example:

```php
use Spatie\LaravelPdf\Facades\Pdf;

Pdf::view('pdfs.invoice', ['invoice' => $invoice])
    ->format('a4')
    ->save('invoice.pdf')
```

This will render the Blade view `pdfs.invoice` with the given data and save it as a PDF file.

You can also return the PDF as a response from your controller:

```php
use Spatie\LaravelPdf\Facades\Pdf;

class DownloadInvoiceController
{
    public function __invoke(Invoice $invoice)
    {
        return Pdf::view('pdfs.invoice', ['invoice' => $invoice])
            ->format('a4')
            ->name('your-invoice.pdf');
    }
}
```

You can use also test your PDFs:

```php
use Spatie\LaravelPdf\Facades\Pdf;

it('can render an invoice', function () {
    Pdf::fake();

    $invoice = Invoice::factory()->create();

    $this->get(route('download-invoice', $invoice))
        ->assertOk();
        
    Pdf::assertRespondedWithPdf(function (PdfBuilder $pdf) {
        return $pdf->contains('test');
    });
});
```

## Support us

[<img src="https://github-ads.s3.eu-central-1.amazonaws.com/laravel-pdf.jpg?t=1" width="419px" />](https://spatie.be/github-ad-click/laravel-pdf)

We invest a lot of resources into creating [best in class open source packages](https://spatie.be/open-source). You can support us by [buying one of our paid products](https://spatie.be/open-source/support-us).

We highly appreciate you sending us a postcard from your hometown, mentioning which of our package(s) you are using. You'll find our address on [our contact page](https://spatie.be/about-us). We publish all received postcards on [our virtual postcard wall](https://spatie.be/open-source/postcards).

## Documentation

All documentation is available [on our documentation site](https://spatie.be/docs/laravel-pdf).

## Testing

For running the testsuite, you'll need to have Puppeteer installed. Pleaser refer to the Browsershot requirements [here](https://spatie.be/docs/browsershot/v4/requirements). Usually `npm -g i puppeteer` will do the trick.

Additionally, you'll need the `pdftotext` CLI which is part of the poppler-utils package. More info can be found in in the [spatie/pdf-to-text readme](https://github.com/spatie/pdf-to-text?tab=readme-ov-file#requirements). Usually `brew install poppler-utils` will suffice.

Finally run the tests with:

```bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Security Vulnerabilities

Please review [our security policy](../../security/policy) on how to report security vulnerabilities.

## Credits

- [Freek Van der Herten](https://github.com/freekmurze)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

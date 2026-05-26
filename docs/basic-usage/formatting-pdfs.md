---
title: Formatting PDFs
weight: 3
---

There are various options to customize the output of the PDFs. You can change the page size, the orientation, the margins, and much more!

## Setting a header and footer

You can set a header and footer on every page of the PDF. You can use the `headerView` and `footerView` methods to set the HTML for the header and footer.

```php
use Spatie\LaravelPdf\Facades\Pdf;

Pdf::view('pdf.invoice', ['invoice' => $invoice])
    ->headerView('pdf.invoice.header')
    ->footerView('pdf.invoice.footer')
    ->save('/some/directory/invoice-april-2022.pdf');
```

You can also use the `headerHtml` and `footerHtml` methods to set the HTML for the header and footer.

```php
use Spatie\LaravelPdf\Facades\Pdf;

Pdf::view('pdf.invoice', ['invoice' => $invoice])
    ->headerHtml('<div>My header</div>')
    ->footerHtml('<div>My footer</div>')
    ->save('/some/directory/invoice-april-2022.pdf');
```

The header and footer do not use the CSS set in the main view. In your header and footer HTML, you should add any CSS you need. Here's an example footer view:

```html
<style>
footer {
    font-size: 12px;
}
</style>

<footer>
  This is the footer
</footer>
```

Inside the footer, you can use the following Blade directives:

- `@pageNumber`:  The current page number
- `@totalPages`:  The total number of pages

> `@pageNumber` and `@totalPages` only work with the Browsershot, Cloudflare and Chrome drivers. The DOMPDF driver does not support these directives.

### Display Images in Headers and Footers

You can add an image using the blade directive `@inlinedImage`

It supports absolute and relative paths

```php
// using relative path
@php $logo = public_path('assets/logo.png'); @endphp
@inlinedImage($logo)

// using absolute path
@inlinedImage('https://some-url/assets/some-logo.png')
```

## Page orientation

By default, all PDFs are created in portrait mode. You can change this by calling the `landscape` method.

```php
use Spatie\LaravelPdf\Facades\Pdf;

Pdf::view('pdf.invoice', ['invoice' => $invoice])
    ->landscape()
    ->save('/some/directory/invoice-april-2022.pdf');
```

Alternatively, you can use the `Orientation` method.

```php
use Spatie\LaravelPdf\Facades\Pdf;
use \Spatie\LaravelPdf\Enums\Orientation;

Pdf::view('pdf.invoice', ['invoice' => $invoice])
    ->orientation(Orientation::Landscape)
    ->save('/some/directory/invoice-april-2022.pdf');
```

## Paper format

By default, all PDFs are created in Letter format. You can change this by calling the `format` method.

```php
use Spatie\LaravelPdf\Facades\Pdf;
use Spatie\LaravelPdf\Enums\Format;

Pdf::view('pdf.invoice', ['invoice' => $invoice])
    ->format(Format::A3) // or you can pass a string like 'a3'
    ->save('/some/directory/invoice-april-2022.pdf');
```

There are the available formats of the `Format` enum:

```php
Letter: 8.5in  x  11in
Legal: 8.5in  x  14in
Tabloid: 11in  x  17in
Ledger: 17in  x  11in
A0: 33.1in  x  46.8in
A1: 23.4in  x  33.1in
A2: 16.54in  x  23.4in
A3: 11.7in  x  16.54in
A4: 8.27in  x  11.7in
A5: 5.83in  x  8.27in
A6: 4.13in  x  5.83in
```

## Paper size

If you don't want to use standardized formats, you can also use the `paperSize` method instead.

```php
use Spatie\LaravelPdf\Facades\Pdf;

Pdf::view('pdf.receipt', ['order' => $order])
    ->paperSize(57, 500, 'mm')
    ->save('/some/directory/receipt-12345.pdf');
```

## Page margins

Margins can be set using the `margins` method. The unit of the margins is millimeters by default.

```php
use Spatie\LaravelPdf\Facades\Pdf;

Pdf::view('pdf.invoice', ['invoice' => $invoice])
    ->margins($top, $right, $bottom, $left)
    ->save('/some/directory/invoice-april-2022.pdf');
```

Optionally you can give a custom unit to the `margins` as the fifth parameter.


```php
use Spatie\LaravelPdf\Facades\Pdf;
use Spatie\LaravelPdf\Enums\Unit;

Pdf::view('pdf.invoice', ['invoice' => $invoice])
    ->margins($top, $right, $bottom, $left, Unit::Pixel)
    ->save('/some/directory/invoice-april-2022.pdf');
```

## Scale

You can scale the content of the PDF using the `scale` method. The value must be between 0.1 and 2.0.

```php
use Spatie\LaravelPdf\Facades\Pdf;

Pdf::view('pdf.invoice', ['invoice' => $invoice])
    ->scale(0.75)
    ->save('/some/directory/invoice.pdf');
```

> Scale is supported by the Browsershot, Cloudflare and Chrome drivers. The DOMPDF driver does not support this option.

## Page ranges

You can select specific pages to include in the output using the `pageRanges` method. The format supports individual pages and ranges.

```php
use Spatie\LaravelPdf\Facades\Pdf;

Pdf::view('pdf.report', ['report' => $report])
    ->pageRanges('1-3, 5')
    ->save('/some/directory/report.pdf');
```

> Page ranges are supported by the Browsershot, Cloudflare and Chrome drivers. The DOMPDF driver does not support this option.

## Tagged PDF

You can generate a tagged (accessible) PDF using the `tagged` method. Tagged PDFs include structural information that makes them accessible to screen readers.

```php
use Spatie\LaravelPdf\Facades\Pdf;

Pdf::view('pdf.invoice', ['invoice' => $invoice])
    ->tagged()
    ->save('/some/directory/invoice.pdf');
```

> Tagged PDFs are supported by the Browsershot and Cloudflare drivers. The DOMPDF driver does not support this option.

## PDF metadata

You can set PDF document metadata such as title, author, subject, and keywords using the `meta` method. This metadata is displayed in PDF viewers — for example, the title is shown in the browser tab when viewing a PDF inline.

```php
use Spatie\LaravelPdf\Facades\Pdf;

Pdf::view('pdf.invoice', ['invoice' => $invoice])
    ->meta(
        title: 'Invoice #123',
        author: 'Acme Corp',
        subject: 'Monthly invoice',
        keywords: 'invoice, acme, april',
        creator: 'My Application',
    )
    ->save('/some/directory/invoice.pdf');
```

All parameters are optional — only the fields you specify will be included in the PDF.

You can also set a creation date. It accepts a `DateTimeInterface` instance (such as a Carbon date) or a raw PDF date string.

```php
use Spatie\LaravelPdf\Facades\Pdf;

Pdf::view('pdf.invoice', ['invoice' => $invoice])
    ->meta(title: 'Invoice #123', creationDate: now())
    ->save('/some/directory/invoice.pdf');
```

Metadata works with all drivers (Browsershot, Cloudflare, and DOMPDF).

## Password protection

You can password-protect a generated PDF with the `encrypt` method. The PDF is encrypted with AES-256 in pure PHP after it is rendered, so no external binaries (such as `qpdf`) are required. This also works on platforms where you cannot install binaries, like Laravel Cloud.

This feature requires the `tecnickcom/tc-lib-pdf-encrypt` package:

```bash
composer require tecnickcom/tc-lib-pdf-encrypt
```

Pass a user password to require it before the document can be opened.

```php
use Spatie\LaravelPdf\Facades\Pdf;

Pdf::view('pdf.invoice', ['invoice' => $invoice])
    ->encrypt(userPassword: 'open-sesame')
    ->save('/some/directory/invoice.pdf');
```

### Owner password and permissions

You can also set an owner password and restrict what readers may do with the document. The owner password grants full access (including changing the permissions), while the user password opens the document with the restrictions applied.

Permissions are passed as a list of `Permission` cases. Only the permissions you pass are granted. When you do not pass any permissions, every permission is granted and the password only controls opening the document.

```php
use Spatie\LaravelPdf\Enums\Permission;
use Spatie\LaravelPdf\Facades\Pdf;

Pdf::view('pdf.invoice', ['invoice' => $invoice])
    ->encrypt(
        userPassword: 'open-sesame',
        ownerPassword: 'owner-secret',
        permissions: [Permission::Print, Permission::Copy],
    )
    ->save('/some/directory/invoice.pdf');
```

The available permissions are `Print`, `Copy`, `Modify`, `Annotate`, `FillForms`, `Extract`, `Assemble`, and `PrintHighResolution`.

Because the document is encrypted in place rather than rebuilt, links, the document tag tree (see [Tagged PDF](#tagged-pdf)), and metadata set with `meta` are preserved.

> When you generate a protected PDF on the queue with `saveQueued`, the passwords are stored in the queue payload until the job runs. Make sure your queue storage (database, Redis) is secured accordingly.

### Supported drivers

Encryption is applied as a post-processing step, so it works with any driver that produces a PDF using a classic cross-reference table. This includes Browsershot, the Chrome driver, and DOMPDF. PDFs that use compressed object streams (which some WeasyPrint output uses) are not supported by the default encrypter, and a `CouldNotEncryptPdf` exception is thrown. In that case you can bind your own encrypter (see below).

### Decrypting a PDF

You can decrypt a protected PDF with the `decrypt` method on the facade. It accepts either a path to a PDF file or the raw PDF contents, together with the user or owner password, and returns the decrypted PDF.

```php
use Spatie\LaravelPdf\Facades\Pdf;

// Pass a path...
$decrypted = Pdf::decrypt('/some/directory/invoice.pdf', 'open-sesame');

// ...or the raw contents
$decrypted = Pdf::decrypt($contents, 'open-sesame');
```

When the password is incorrect, a `Spatie\LaravelPdf\Exceptions\CouldNotDecryptPdf` exception is thrown, so you can catch it to handle a wrong password.

```php
use Spatie\LaravelPdf\Exceptions\CouldNotDecryptPdf;
use Spatie\LaravelPdf\Facades\Pdf;

try {
    $decrypted = Pdf::decrypt('/some/directory/invoice.pdf', $password);
} catch (CouldNotDecryptPdf $exception) {
    // The password was incorrect, or the PDF could not be decrypted.
}
```

### Using a custom encrypter

The default encrypter handles AES-256 protection. If you want to use a different strategy (for example a `qpdf` binary or a commercial library), implement the `Spatie\LaravelPdf\Encryption\PdfEncrypter` contract and point the `encrypter` config key to your class.

```php
// config/laravel-pdf.php

'encrypter' => App\Pdf\MyEncrypter::class,
```

```php
namespace App\Pdf;

use Spatie\LaravelPdf\Encryption\PdfEncrypter;
use Spatie\LaravelPdf\Encryption\PdfEncryption;

class MyEncrypter implements PdfEncrypter
{
    public function encrypt(string $pdf, PdfEncryption $encryption): string
    {
        // return the encrypted PDF
    }

    public function decrypt(string $pdf, string $password): string
    {
        // return the decrypted PDF
    }
}
```

## Conditional formatting

You can conditionally apply formatting options using the `when` and `unless` methods. This is useful when you want to change the PDF output based on some condition without breaking the method chain.

```php
use Spatie\LaravelPdf\Facades\Pdf;

Pdf::view('pdf.invoice', ['invoice' => $invoice])
    ->format('a4')
    ->when($invoice->isLandscape(), fn ($pdf) => $pdf->landscape())
    ->when($invoice->hasLetterhead(), function ($pdf) use ($invoice) {
        $pdf->headerView('pdf.letterhead', ['company' => $invoice->company]);
    })
    ->save('/some/directory/invoice.pdf');
```

The `unless` method works the opposite way:

```php
use Spatie\LaravelPdf\Facades\Pdf;

Pdf::view('pdf.report', ['report' => $report])
    ->unless($report->isCompact(), fn ($pdf) => $pdf->margins(20, 15, 20, 15))
    ->save('/some/directory/report.pdf');
```

## Debugging

You can call `dump` or `dd` on the builder to inspect its current state:

```php
use Spatie\LaravelPdf\Facades\Pdf;

Pdf::view('pdf.invoice', ['invoice' => $invoice])
    ->format('a4')
    ->landscape()
    ->dump() // dumps the builder state and continues
    ->save('/some/directory/invoice.pdf');
```

## Background color

By default, the resulting PDF will not show the background of the html page.

You can set a background using css :

```html
<style>
    html {
        -webkit-print-color-adjust: exact;
    }
</style>
```

Alternatively you can set `print-color-adjust` as `economy` it would generate the pdf document in economy mode.

Or you can set a transparent background using browsershot:

```php
Pdf::view('test')
    ->withBrowsershot(function (Browsershot $browsershot) {
        $browsershot->transparentBackground();
    })
    ->save($this->targetPath);
```

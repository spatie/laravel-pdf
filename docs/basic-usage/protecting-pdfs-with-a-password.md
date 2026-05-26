---
title: Protecting PDFs with a password
weight: 9
---

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

## Owner password and permissions

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

Because the document is encrypted in place rather than rebuilt, links, the document tag tree, and metadata set with `meta` are preserved.

> When you generate a protected PDF on the queue with `saveQueued`, the passwords are stored in the queue payload until the job runs. Make sure your queue storage (database, Redis) is secured accordingly.

## Supported drivers

Encryption is applied as a post-processing step, so it works with any driver that produces a PDF using a classic cross-reference table. This includes Browsershot, the Chrome driver, and DOMPDF. PDFs that use compressed object streams (which some WeasyPrint output uses) are not supported by the default encrypter, and a `CouldNotEncryptPdf` exception is thrown. In that case you can bind your own encrypter (see below).

## Decrypting a PDF

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

## Using a custom encrypter

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

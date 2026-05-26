<?php

namespace Spatie\LaravelPdf\Exceptions;

use Exception;
use Spatie\LaravelPdf\Encryption\PdfEncrypter;

class CouldNotEncryptPdf extends Exception
{
    public static function packageNotInstalled(): self
    {
        return new self(
            'The tecnickcom/tc-lib-pdf-encrypt package is required to encrypt or decrypt PDFs. '
            .'Install it with: composer require tecnickcom/tc-lib-pdf-encrypt'
        );
    }

    public static function unsupportedStructure(): self
    {
        return new self(
            'This PDF uses compressed object streams or cross-reference streams, which the default '
            .'encrypter cannot rewrite. Bind your own implementation of '
            .PdfEncrypter::class.' to encrypt this document.'
        );
    }

    public static function couldNotParse(string $reason): self
    {
        return new self("The PDF could not be parsed for encryption: {$reason}.");
    }

    public static function invalidPassword(): self
    {
        return new self('The provided password could not decrypt the PDF.');
    }

    public static function unsupportedHandler(): self
    {
        return new self(
            'The default encrypter can only decrypt AES-256 (revision 6) PDFs. '
            .'Bind your own implementation of '.PdfEncrypter::class.' to decrypt this document.'
        );
    }
}

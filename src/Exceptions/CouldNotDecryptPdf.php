<?php

namespace Spatie\LaravelPdf\Exceptions;

use Exception;
use Spatie\LaravelPdf\Encryption\PdfEncrypter;

class CouldNotDecryptPdf extends Exception
{
    public static function invalidPassword(): self
    {
        return new self('The provided password could not decrypt the PDF.');
    }

    public static function notEncrypted(): self
    {
        return new self('The PDF could not be decrypted because it is not encrypted.');
    }

    public static function missingEncryptionDictionary(): self
    {
        return new self('The PDF could not be decrypted because its encryption dictionary is missing.');
    }

    public static function unsupportedHandler(): self
    {
        return new self(
            'The default encrypter can only decrypt AES-256 (revision 6) PDFs. '
            .'Bind your own implementation of '.PdfEncrypter::class.' to decrypt this document.'
        );
    }

    public static function fileNotFound(string $path): self
    {
        return new self("The PDF file `{$path}` could not be found.");
    }
}

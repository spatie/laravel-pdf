<?php

namespace Spatie\LaravelPdf\Encryption;

interface PdfEncrypter
{
    public function encrypt(string $pdf, PdfEncryption $encryption): string;

    public function decrypt(string $pdf, #[\SensitiveParameter] string $password): string;
}

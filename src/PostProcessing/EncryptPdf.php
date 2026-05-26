<?php

namespace Spatie\LaravelPdf\PostProcessing;

use Spatie\LaravelPdf\Encryption\PdfEncrypter;
use Spatie\LaravelPdf\Encryption\PdfEncryption;

class EncryptPdf implements PdfPostProcessor
{
    public function __construct(
        protected PdfEncryption $encryption,
    ) {}

    public function process(string $pdf): string
    {
        return app(PdfEncrypter::class)->encrypt($pdf, $this->encryption);
    }
}

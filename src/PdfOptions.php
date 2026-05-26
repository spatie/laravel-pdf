<?php

namespace Spatie\LaravelPdf;

use Spatie\LaravelPdf\Encryption\PdfEncryption;

class PdfOptions
{
    public ?string $format = null;

    public ?array $paperSize = null;

    public ?array $margins = null;

    public ?string $orientation = null;

    public ?float $scale = null;

    public ?string $pageRanges = null;

    public bool $tagged = false;

    public ?PdfEncryption $encryption = null;
}

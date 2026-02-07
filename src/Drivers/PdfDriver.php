<?php

namespace Spatie\LaravelPdf\Drivers;

use Spatie\LaravelPdf\PdfOptions;

interface PdfDriver
{
    public function generatePdf(string $html, ?string $headerHtml, ?string $footerHtml, PdfOptions $options): string;

    public function savePdf(string $html, ?string $headerHtml, ?string $footerHtml, PdfOptions $options, string $path): void;
}

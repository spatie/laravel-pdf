<?php

namespace Spatie\LaravelPdf\Tests\TestSupport;

use Spatie\LaravelPdf\Drivers\PdfDriver;
use Spatie\LaravelPdf\Drivers\SupportsReadiness;
use Spatie\LaravelPdf\PdfOptions;

class FakeDriver implements PdfDriver, SupportsReadiness
{
    public int $generateCount = 0;

    public int $saveCount = 0;

    public ?PdfOptions $lastOptions = null;

    public ?string $lastHtml = null;

    public function __construct(public string $content = '%PDF-fake') {}

    public function generatePdf(string $html, ?string $headerHtml, ?string $footerHtml, PdfOptions $options): string
    {
        $this->generateCount++;
        $this->lastOptions = $options;
        $this->lastHtml = $html;

        return $this->content;
    }

    public function savePdf(string $html, ?string $headerHtml, ?string $footerHtml, PdfOptions $options, string $path): void
    {
        $this->saveCount++;
        $this->lastOptions = $options;
        $this->lastHtml = $html;

        file_put_contents($path, $this->content);
    }
}

<?php

use Spatie\LaravelPdf\Drivers\PdfDriver;
use Spatie\LaravelPdf\Facades\Pdf;
use Spatie\LaravelPdf\PdfOptions;
use Spatie\LaravelPdf\PostProcessing\PasswordProtectionPostProcessor;

it('password protects pdf content', function () {
    $protectedPdf = (new PasswordProtectionPostProcessor('secret'))->process(testPdfContent());

    expect($protectedPdf)
        ->toStartWith('%PDF')
        ->toContain('/Encrypt');
});

it('runs password protection after a driver generates pdf content', function () {
    $driver = new class implements PdfDriver
    {
        public int $generateCalls = 0;

        public int $saveCalls = 0;

        public function generatePdf(string $html, ?string $headerHtml, ?string $footerHtml, PdfOptions $options): string
        {
            $this->generateCalls++;

            return testPdfContent();
        }

        public function savePdf(string $html, ?string $headerHtml, ?string $footerHtml, PdfOptions $options, string $path): void
        {
            $this->saveCalls++;

            file_put_contents($path, testPdfContent());
        }
    };

    $targetPath = getTempPath('protected.pdf');

    Pdf::html('<h1>Hello</h1>')
        ->setDriver($driver)
        ->password('secret')
        ->save($targetPath);

    expect($driver->generateCalls)->toBe(1)
        ->and($driver->saveCalls)->toBe(0)
        ->and(file_get_contents($targetPath))->toContain('/Encrypt');
});

it('stores the password on queued pdf options', function () {
    Pdf::fake();

    Pdf::html('<h1>Hello</h1>')
        ->password('secret')
        ->saveQueued('protected.pdf');

    Pdf::assertQueued(function ($pdf) {
        return invade($pdf)->buildOptions()->password === 'secret';
    });
});

function testPdfContent(): string
{
    $pdf = new FPDF;
    $pdf->AddPage();
    $pdf->SetFont('Arial', '', 12);
    $pdf->Cell(40, 10, 'Hello');

    return $pdf->Output('S');
}

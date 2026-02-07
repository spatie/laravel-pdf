<?php

use Illuminate\Support\Facades\Config;
use Spatie\LaravelPdf\Drivers\DomPdfDriver;
use Spatie\LaravelPdf\Drivers\PdfDriver;
use Spatie\LaravelPdf\Enums\Orientation;
use Spatie\LaravelPdf\Facades\Pdf;
use Spatie\LaravelPdf\PdfOptions;

it('generates a pdf with default options', function () {
    $driver = new DomPdfDriver;

    $result = $driver->generatePdf('<h1>Hello</h1>', null, null, new PdfOptions);

    expect($result)->toStartWith('%PDF');
});

it('saves a pdf to disk', function () {
    $driver = new DomPdfDriver;

    $path = getTempPath('dompdf-save-test.pdf');
    $driver->savePdf('<h1>Hello</h1>', null, null, new PdfOptions, $path);

    expect(file_exists($path))->toBeTrue();
    expect(file_get_contents($path))->toStartWith('%PDF');
});

it('generates a pdf with text content', function () {
    $driver = new DomPdfDriver;

    $path = getTempPath('dompdf-text-content.pdf');
    $driver->savePdf(
        '<html><body><h1>Invoice #123</h1><p>Total: $99.00</p></body></html>',
        null,
        null,
        new PdfOptions,
        $path,
    );

    expect($path)->toContainText(['Invoice', '123', '99']);
});

it('generates a pdf with a4 format', function () {
    $driver = new DomPdfDriver;

    $options = new PdfOptions;
    $options->format = 'a4';

    $path = getTempPath('dompdf-a4.pdf');
    $driver->savePdf('<html><body><h1>A4 Document</h1></body></html>', null, null, $options, $path);

    expect($path)->toContainText('A4 Document');
});

it('generates a pdf with letter format', function () {
    $driver = new DomPdfDriver;

    $options = new PdfOptions;
    $options->format = 'letter';

    $path = getTempPath('dompdf-letter.pdf');
    $driver->savePdf('<html><body><h1>Letter Document</h1></body></html>', null, null, $options, $path);

    expect($path)->toContainText('Letter Document');
});

it('generates a landscape pdf', function () {
    $driver = new DomPdfDriver;

    $options = new PdfOptions;
    $options->orientation = Orientation::Landscape->value;

    $path = getTempPath('dompdf-landscape.pdf');
    $driver->savePdf('<html><body><h1>Landscape</h1></body></html>', null, null, $options, $path);

    expect($path)->toContainText('Landscape');
});

it('generates a pdf with margins', function () {
    $driver = new DomPdfDriver;

    $options = new PdfOptions;
    $options->margins = [
        'top' => 20,
        'right' => 15,
        'bottom' => 20,
        'left' => 15,
        'unit' => 'mm',
    ];

    $path = getTempPath('dompdf-margins.pdf');
    $driver->savePdf(
        '<html><head></head><body><h1>With Margins</h1></body></html>',
        null,
        null,
        $options,
        $path,
    );

    expect($path)->toContainText('With Margins');
});

it('generates a pdf with custom paper size', function () {
    $driver = new DomPdfDriver;

    $options = new PdfOptions;
    $options->paperSize = [
        'width' => 100,
        'height' => 200,
        'unit' => 'mm',
    ];

    $path = getTempPath('dompdf-custom-size.pdf');
    $driver->savePdf(
        '<html><body><h1>Custom Size</h1></body></html>',
        null,
        null,
        $options,
        $path,
    );

    expect($path)->toContainText('Custom Size');
});

it('generates a pdf with header and footer', function () {
    $driver = new DomPdfDriver;

    $path = getTempPath('dompdf-header-footer.pdf');
    $driver->savePdf(
        '<html><body><p>Main content here</p></body></html>',
        '<div>Company Header</div>',
        '<div>Page Footer</div>',
        new PdfOptions,
        $path,
    );

    expect($path)->toContainText(['Main content', 'Company Header', 'Page Footer']);
});

it('generates a pdf with styled html', function () {
    $driver = new DomPdfDriver;

    $html = <<<'HTML'
    <html>
    <head>
        <style>
            body { font-family: sans-serif; }
            .invoice { border: 1px solid #000; padding: 20px; }
            .total { font-weight: bold; font-size: 24px; }
        </style>
    </head>
    <body>
        <div class="invoice">
            <h1>Invoice</h1>
            <p>Item: Widget</p>
            <p class="total">Total: $150.00</p>
        </div>
    </body>
    </html>
    HTML;

    $path = getTempPath('dompdf-styled.pdf');
    $driver->savePdf($html, null, null, new PdfOptions, $path);

    expect($path)->toContainText(['Invoice', 'Widget', '150']);
});

it('generates a pdf with remote enabled config', function () {
    $driver = new DomPdfDriver(['is_remote_enabled' => true]);

    $path = getTempPath('dompdf-remote.pdf');
    $driver->savePdf(
        '<html><body><h1>Remote Enabled</h1></body></html>',
        null,
        null,
        new PdfOptions,
        $path,
    );

    expect($path)->toContainText('Remote Enabled');
});

it('can switch to dompdf driver at runtime', function () {
    Config::set('laravel-pdf.dompdf', []);

    app()->forgetInstance('laravel-pdf.driver.dompdf');

    $path = getTempPath('runtime-switch-dompdf.pdf');

    Pdf::html('<h1>Hello</h1>')
        ->driver('dompdf')
        ->save($path);

    expect(file_exists($path))->toBeTrue();
    expect(file_get_contents($path))->toStartWith('%PDF');
});

it('uses dompdf as default driver when configured', function () {
    Config::set('laravel-pdf.driver', 'dompdf');
    Config::set('laravel-pdf.dompdf', []);

    app()->forgetInstance(PdfDriver::class);
    app()->forgetInstance('laravel-pdf.driver.dompdf');

    $path = getTempPath('default-dompdf.pdf');

    Pdf::html('<h1>Hello</h1>')->save($path);

    expect(file_exists($path))->toBeTrue();
    expect(file_get_contents($path))->toStartWith('%PDF');
});

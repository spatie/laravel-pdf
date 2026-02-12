<?php

use Illuminate\Support\Facades\Config;
use Spatie\LaravelPdf\Drivers\PdfDriver;
use Spatie\LaravelPdf\Drivers\WeasyPrintDriver;
use Spatie\LaravelPdf\Enums\Orientation;
use Spatie\LaravelPdf\Facades\Pdf;
use Spatie\LaravelPdf\PdfOptions;

beforeEach(function () {
    $binary = config('laravel-pdf.weasyprint.binary');

    $resolved = trim(shell_exec('command -v '.escapeshellarg($binary).' 2>/dev/null'));

    if (! $resolved || ! is_executable($resolved)) {
        $this->markTestSkipped('WeasyPrint is not available at: '.$binary);
    }

});
it('generates a pdf with default options', function () {
    $driver = new WeasyPrintDriver(config('laravel-pdf.weasyprint'));

    $result = $driver->generatePdf('<h1>Hello</h1>', null, null, new PdfOptions);

    expect($result)->toStartWith('%PDF');
});

it('saves a pdf to disk', function () {
    $driver = new WeasyPrintDriver(config('laravel-pdf.weasyprint'));

    $path = getTempPath('weasyprint-save-test.pdf');
    $driver->savePdf('<h1>Hello</h1>', null, null, new PdfOptions, $path);

    expect(file_exists($path))->toBeTrue();
    expect(file_get_contents($path))->toStartWith('%PDF');
});

it('generates a pdf with text content', function () {
    $driver = new WeasyPrintDriver(config('laravel-pdf.weasyprint'));

    $path = getTempPath('weasyprint-text-content.pdf');
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
    $driver = new WeasyPrintDriver(config('laravel-pdf.weasyprint'));

    $options = new PdfOptions;
    $options->format = 'a4';

    $path = getTempPath('weasyprint-a4.pdf');
    $driver->savePdf('<html><body><h1>A4 Document</h1></body></html>', null, null, $options, $path);

    expect($path)->toContainText('A4 Document');
});

it('generates a pdf with letter format', function () {
    $driver = new WeasyPrintDriver(config('laravel-pdf.weasyprint'));

    $options = new PdfOptions;
    $options->format = 'letter';

    $path = getTempPath('weasyprint-letter.pdf');
    $driver->savePdf('<html><body><h1>Letter Document</h1></body></html>', null, null, $options, $path);

    expect($path)->toContainText('Letter Document');
});

it('generates a landscape pdf', function () {
    $driver = new WeasyPrintDriver(config('laravel-pdf.weasyprint'));

    $options = new PdfOptions;
    $options->orientation = Orientation::Landscape->value;

    $path = getTempPath('weasyprint-landscape.pdf');
    $driver->savePdf('<html><body><h1>Landscape</h1></body></html>', null, null, $options, $path);

    expect($path)->toContainText('Landscape');
});

it('generates a pdf with margins', function () {
    $driver = new WeasyPrintDriver(config('laravel-pdf.weasyprint'));

    $options = new PdfOptions;
    $options->margins = [
        'top' => 20,
        'right' => 15,
        'bottom' => 20,
        'left' => 15,
        'unit' => 'mm',
    ];

    $path = getTempPath('weasyprint-margins.pdf');
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
    $driver = new WeasyPrintDriver(config('laravel-pdf.weasyprint'));

    $options = new PdfOptions;
    $options->paperSize = [
        'width' => 100,
        'height' => 200,
        'unit' => 'mm',
    ];

    $path = getTempPath('weasyprint-custom-size.pdf');
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
    $driver = new WeasyPrintDriver(config('laravel-pdf.weasyprint'));

    $path = getTempPath('weasyprint-header-footer.pdf');
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
    $driver = new WeasyPrintDriver(config('laravel-pdf.weasyprint'));

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

    $path = getTempPath('weasyprint-styled.pdf');
    $driver->savePdf($html, null, null, new PdfOptions, $path);

    expect($path)->toContainText(['Invoice', 'Widget', '150']);
});

it('can switch to weasyprint driver at runtime', function () {
    Config::set('laravel-pdf.weasyprint', [
        'binary' => config('laravel-pdf.weasyprint.binary'),
    ]);

    app()->forgetInstance('laravel-pdf.driver.weasyprint');

    $path = getTempPath('runtime-switch-weasyprint.pdf');

    Pdf::html('<h1>Hello</h1>')
        ->driver('weasyprint')
        ->save($path);

    expect(file_exists($path))->toBeTrue();
    expect(file_get_contents($path))->toStartWith('%PDF');
});

it('generates a pdf with metadata', function () {
    $driver = new WeasyPrintDriver(config('laravel-pdf.weasyprint'));

    $result = $driver->generatePdf('<h1>Hello</h1>', null, null, new PdfOptions);

    $metadata = new \Spatie\LaravelPdf\PdfMetadata(
        title: 'Invoice #123',
        author: 'Acme Corp',
        subject: 'Monthly Invoice',
        keywords: 'invoice, acme',
    );

    $result = \Spatie\LaravelPdf\PdfMetadataWriter::write($result, $metadata);

    expect($result)
        ->toStartWith('%PDF')
        ->toContain('/Title (Invoice #123)')
        ->toContain('/Author (Acme Corp)')
        ->toContain('/Subject (Monthly Invoice)')
        ->toContain('/Keywords (invoice, acme)');
});

it('saves a pdf with metadata via the facade', function () {
    Config::set('laravel-pdf.driver', 'weasyprint');
    Config::set('laravel-pdf.weasyprint', [
        'binary' => config('laravel-pdf.weasyprint.binary'),
    ]);
    app()->forgetInstance(PdfDriver::class);
    app()->forgetInstance('laravel-pdf.driver.weasyprint');

    $path = getTempPath('weasyprint-metadata-facade.pdf');

    Pdf::html('<h1>Hello</h1>')
        ->meta(title: 'Facade Title', author: 'Test Author')
        ->save($path);

    expect(file_exists($path))->toBeTrue();

    $content = file_get_contents($path);
    expect($content)
        ->toStartWith('%PDF')
        ->toContain('/Title (Facade Title)')
        ->toContain('/Author (Test Author)');
});

it('saves a pdf with metadata and creation date via the facade', function () {
    Config::set('laravel-pdf.driver', 'weasyprint');
    Config::set('laravel-pdf.weasyprint', [
        'binary' => config('laravel-pdf.weasyprint.binary'),
    ]);
    app()->forgetInstance(PdfDriver::class);
    app()->forgetInstance('laravel-pdf.driver.weasyprint');

    $path = getTempPath('weasyprint-metadata-date.pdf');
    $date = new DateTimeImmutable('2026-01-15 10:30:00', new DateTimeZone('UTC'));

    Pdf::html('<h1>Dated PDF</h1>')
        ->meta(title: 'Dated Document', creationDate: $date)
        ->save($path);

    $content = file_get_contents($path);
    expect($content)
        ->toContain('/Title (Dated Document)')
        ->toContain("/CreationDate (D:20260115103000+00'00')");
});

it('uses weasyprint as default driver when configured', function () {
    Config::set('laravel-pdf.driver', 'weasyprint');
    Config::set('laravel-pdf.weasyprint', [
        'binary' => config('laravel-pdf.weasyprint.binary'),
    ]);
    app()->forgetInstance(PdfDriver::class);
    app()->forgetInstance('laravel-pdf.driver.weasyprint');

    $path = getTempPath('default-weasyprint.pdf');

    Pdf::html('<h1>Hello</h1>')->save($path);

    expect(file_exists($path))->toBeTrue();
    expect(file_get_contents($path))->toStartWith('%PDF');
});

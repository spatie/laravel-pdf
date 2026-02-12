<?php

use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Spatie\LaravelPdf\Drivers\GotenbergDriver;
use Spatie\LaravelPdf\Drivers\PdfDriver;
use Spatie\LaravelPdf\Enums\Orientation;
use Spatie\LaravelPdf\Facades\Pdf;
use Spatie\LaravelPdf\PdfMetadata;
use Spatie\LaravelPdf\PdfMetadataWriter;
use Spatie\LaravelPdf\PdfOptions;

beforeEach(function () {
    $this->gotenbergUrl = env('GOTENBERG_URL', 'http://localhost:3000');

    try {
        $response = Http::get("{$this->gotenbergUrl}/health");

        if (! $response->successful()) {
            $this->markTestSkipped('Gotenberg is not running.');
        }
    } catch (Exception $exception) {
        $this->markTestSkipped('Gotenberg is not available: '.$exception->getMessage());
    }

    $this->driver = new GotenbergDriver([
        'url' => $this->gotenbergUrl,
    ]);
});

it('generates a pdf with default options', function () {
    $result = $this->driver->generatePdf('<h1>Hello</h1>', null, null, new PdfOptions);

    expect($result)->toStartWith('%PDF');
});

it('saves a pdf to disk', function () {
    $path = getTempPath('gotenberg-save-test.pdf');
    $this->driver->savePdf('<h1>Hello</h1>', null, null, new PdfOptions, $path);

    expect(file_exists($path))->toBeTrue();
    expect(file_get_contents($path))->toStartWith('%PDF');
});

it('generates a pdf with text content', function () {
    $path = getTempPath('gotenberg-text-content.pdf');
    $this->driver->savePdf(
        '<html><body><h1>Invoice #123</h1><p>Total: $99.00</p></body></html>',
        null,
        null,
        new PdfOptions,
        $path,
    );

    expect($path)->toContainText(['Invoice', '123', '99']);
});

it('generates a pdf with a4 format', function () {
    $options = new PdfOptions;
    $options->format = 'a4';

    $path = getTempPath('gotenberg-a4.pdf');
    $this->driver->savePdf('<html><body><h1>A4 Document</h1></body></html>', null, null, $options, $path);

    expect($path)->toContainText('A4 Document');
});

it('generates a landscape pdf', function () {
    $options = new PdfOptions;
    $options->orientation = Orientation::Landscape->value;

    $path = getTempPath('gotenberg-landscape.pdf');
    $this->driver->savePdf('<html><body><h1>Landscape</h1></body></html>', null, null, $options, $path);

    expect($path)->toContainText('Landscape');
});

it('generates a pdf with margins', function () {
    $options = new PdfOptions;
    $options->margins = [
        'top' => 20,
        'right' => 15,
        'bottom' => 20,
        'left' => 15,
        'unit' => 'mm',
    ];

    $path = getTempPath('gotenberg-margins.pdf');
    $this->driver->savePdf(
        '<html><head></head><body><h1>With Margins</h1></body></html>',
        null,
        null,
        $options,
        $path,
    );

    expect($path)->toContainText('With Margins');
});

it('generates a pdf with custom paper size', function () {
    $options = new PdfOptions;
    $options->paperSize = [
        'width' => 100,
        'height' => 200,
        'unit' => 'mm',
    ];

    $path = getTempPath('gotenberg-custom-size.pdf');
    $this->driver->savePdf(
        '<html><body><h1>Custom Size</h1></body></html>',
        null,
        null,
        $options,
        $path,
    );

    expect($path)->toContainText('Custom Size');
});

it('generates a pdf with header and footer', function () {
    $path = getTempPath('gotenberg-header-footer.pdf');
    $this->driver->savePdf(
        '<html><body><p>Main content here</p></body></html>',
        '<html><body><div>Company Header</div></body></html>',
        '<html><body><div>Page Footer</div></body></html>',
        new PdfOptions,
        $path,
    );

    expect($path)->toContainText(['Main content', 'Company Header', 'Page Footer']);
});

it('generates a pdf with styled html', function () {
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

    $path = getTempPath('gotenberg-styled.pdf');
    $this->driver->savePdf($html, null, null, new PdfOptions, $path);

    expect($path)->toContainText(['Invoice', 'Widget', '150']);
});

it('can switch to gotenberg driver at runtime', function () {
    Config::set('laravel-pdf.gotenberg', [
        'url' => $this->gotenbergUrl,
    ]);

    app()->forgetInstance('laravel-pdf.driver.gotenberg');

    $path = getTempPath('runtime-switch-gotenberg.pdf');

    Pdf::html('<h1>Hello from Gotenberg</h1>')
        ->driver('gotenberg')
        ->save($path);

    expect(file_exists($path))->toBeTrue();
    expect(file_get_contents($path))->toStartWith('%PDF');
});

it('uses gotenberg as default driver when configured', function () {
    Config::set('laravel-pdf.driver', 'gotenberg');
    Config::set('laravel-pdf.gotenberg', [
        'url' => $this->gotenbergUrl,
    ]);

    app()->forgetInstance(PdfDriver::class);
    app()->forgetInstance('laravel-pdf.driver.gotenberg');

    $path = getTempPath('default-gotenberg.pdf');

    Pdf::html('<h1>Hello</h1>')->save($path);

    expect(file_exists($path))->toBeTrue();
    expect(file_get_contents($path))->toStartWith('%PDF');
});

it('uses basic auth when configured', function () {
    Config::set('laravel-pdf.driver', 'gotenberg');
    Config::set('laravel-pdf.gotenberg', [
        'url' => $this->gotenbergUrl,
        'username' => 'testuser',
        'password' => 'testpass',
    ]);

    app()->forgetInstance(PdfDriver::class);
    app()->forgetInstance('laravel-pdf.driver.gotenberg');

    $path = getTempPath('default-gotenberg.pdf');

    Http::fake();

    Pdf::html('<h1>Hello</h1>')->save($path);

    Http::assertSent(function (Request $request) {
        return $request->header('Authorization')[0] === 'Basic '.base64_encode('testuser:testpass');
    });
});

it('generates a pdf with metadata', function () {
    $result = $this->driver->generatePdf('<h1>Hello</h1>', null, null, new PdfOptions);

    $metadata = new PdfMetadata(
        title: 'Invoice #123',
        author: 'Acme Corp',
        subject: 'Monthly Invoice',
        keywords: 'invoice, acme',
    );

    $result = PdfMetadataWriter::write($result, $metadata);

    expect($result)
        ->toStartWith('%PDF')
        ->toContain('/Title (Invoice #123)')
        ->toContain('/Author (Acme Corp)')
        ->toContain('/Subject (Monthly Invoice)')
        ->toContain('/Keywords (invoice, acme)');
});

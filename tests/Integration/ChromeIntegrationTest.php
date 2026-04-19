<?php

use Spatie\LaravelPdf\Drivers\ChromeDriver;
use Spatie\LaravelPdf\PdfOptions;

beforeEach(function () {
    $this->driver = new ChromeDriver;
});

it('generates a pdf with default options', function () {
    $result = $this->driver->generatePdf('<h1>Hello</h1>', null, null, new PdfOptions);

    expect($result)->toStartWith('%PDF');
});

it('saves a pdf to disk', function () {
    $path = getTempPath('chrome-save-test.pdf');

    $this->driver->savePdf('<h1>Hello</h1>', null, null, new PdfOptions, $path);

    expect(file_exists($path))->toBeTrue();
    expect(file_get_contents($path))->toStartWith('%PDF');
});

it('generates a pdf with text content', function () {
    $path = getTempPath('chrome-text-content.pdf');

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

    $path = getTempPath('chrome-a4.pdf');
    $this->driver->savePdf('<html><body><h1>A4 Document</h1></body></html>', null, null, $options, $path);

    expect($path)->toContainText('A4 Document');
});

it('generates a pdf with header and footer', function () {
    $options = new PdfOptions;

    $path = getTempPath('chrome-header-footer.pdf');
    $this->driver->savePdf(
        '<html><body><h1>Body</h1></body></html>',
        '<h1>Header</h1>',
        '<h1>Footer</h1>',
        $options,
        $path,
    );

    expect($path)->toContainText(['Header', 'Footer']);
});

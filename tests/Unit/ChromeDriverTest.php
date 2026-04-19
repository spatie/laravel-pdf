<?php

use Spatie\LaravelPdf\Drivers\ChromeDriver;
use Spatie\LaravelPdf\Enums\Orientation;
use Spatie\LaravelPdf\PdfOptions;

it('builds default browser options', function () {
    $driver = new ChromeDriver;

    $options = invade($driver)->buildBrowserOptions();

    expect($options)->toBe([
        'headless' => true,
    ]);
});

it('builds configured browser options', function () {
    $driver = new ChromeDriver([
        'no_sandbox' => true,
        'startup_timeout' => 45,
        'user_data_dir' => '/tmp/chrome-profile',
        'custom_flags' => ['--disable-gpu', '--lang=en-US'],
        'env_variables' => ['HOME' => '/tmp/chrome-home'],
    ]);

    $options = invade($driver)->buildBrowserOptions();

    expect($options)->toBe([
        'headless' => true,
        'noSandbox' => true,
        'startupTimeout' => 45,
        'userDataDir' => '/tmp/chrome-profile',
        'customFlags' => ['--disable-gpu', '--lang=en-US'],
        'envVariables' => ['HOME' => '/tmp/chrome-home'],
    ]);
});

it('builds default pdf options', function () {
    $driver = new ChromeDriver;

    $pdfOptions = invade($driver)->buildPdfOptions(null, null, new PdfOptions);

    expect($pdfOptions)->toBe([
        'printBackground' => true,
    ]);
});

it('maps format to paper dimensions', function () {
    $driver = new ChromeDriver;

    $options = new PdfOptions;
    $options->format = 'A4';

    $pdfOptions = invade($driver)->buildPdfOptions(null, null, $options);

    expect($pdfOptions)->toMatchArray([
        'printBackground' => true,
        'paperWidth' => 8.27,
        'paperHeight' => 11.7,
    ]);
});

it('maps custom paper size and margins to inches', function () {
    $driver = new ChromeDriver;

    $options = new PdfOptions;
    $options->paperSize = [
        'width' => 100,
        'height' => 200,
        'unit' => 'mm',
    ];
    $options->margins = [
        'top' => 10,
        'right' => 20,
        'bottom' => 30,
        'left' => 40,
        'unit' => 'mm',
    ];

    $pdfOptions = invade($driver)->buildPdfOptions(null, null, $options);

    expect(round($pdfOptions['paperWidth'], 5))->toBe(3.93701);
    expect(round($pdfOptions['paperHeight'], 5))->toBe(7.87402);
    expect(round($pdfOptions['marginTop'], 6))->toBe(0.393701);
    expect(round($pdfOptions['marginRight'], 6))->toBe(0.787402);
    expect(round($pdfOptions['marginBottom'], 6))->toBe(1.181103);
    expect(round($pdfOptions['marginLeft'], 6))->toBe(1.574804);
});

it('falls back to millimeters for unknown units', function () {
    $driver = new ChromeDriver;

    $options = new PdfOptions;
    $options->paperSize = [
        'width' => 10,
        'height' => 20,
        'unit' => 'unknown',
    ];
    $options->margins = [
        'top' => 1,
        'right' => 2,
        'bottom' => 3,
        'left' => 4,
        'unit' => 'unknown',
    ];

    $pdfOptions = invade($driver)->buildPdfOptions(null, null, $options);

    expect(round($pdfOptions['paperWidth'], 6))->toBe(0.393701);
    expect(round($pdfOptions['paperHeight'], 6))->toBe(0.787402);
    expect(round($pdfOptions['marginTop'], 7))->toBe(0.0393701);
    expect(round($pdfOptions['marginRight'], 7))->toBe(0.0787402);
    expect(round($pdfOptions['marginBottom'], 7))->toBe(0.1181103);
    expect(round($pdfOptions['marginLeft'], 7))->toBe(0.1574804);
});

it('maps landscape scale page and ranges', function () {
    $driver = new ChromeDriver;

    $options = new PdfOptions;
    $options->orientation = Orientation::Landscape->value;
    $options->scale = 0.75;
    $options->pageRanges = '1-3';

    $pdfOptions = invade($driver)->buildPdfOptions(null, null, $options);

    expect($pdfOptions)->toMatchArray([
        'landscape' => true,
        'scale' => 0.75,
        'pageRanges' => '1-3',
    ]);
});

it('enables header and footer templates when provided', function () {
    $driver = new ChromeDriver;

    $pdfOptions = invade($driver)->buildPdfOptions(
        '<div>Header</div>',
        '<div>Footer</div>',
        new PdfOptions,
    );

    expect($pdfOptions)->toMatchArray([
        'displayHeaderFooter' => true,
        'headerTemplate' => '<div>Header</div>',
        'footerTemplate' => '<div>Footer</div>',
    ]);
});

it('enables header footer display when only one template is provided', function () {
    $driver = new ChromeDriver;

    $headerOptions = invade($driver)->buildPdfOptions('<div>Header</div>', null, new PdfOptions);
    $footerOptions = invade($driver)->buildPdfOptions(null, '<div>Footer</div>', new PdfOptions);

    expect($headerOptions)->toMatchArray([
        'displayHeaderFooter' => true,
        'headerTemplate' => '<div>Header</div>',
    ]);

    expect($headerOptions)->not->toHaveKey('footerTemplate');

    expect($footerOptions)->toMatchArray([
        'displayHeaderFooter' => true,
        'footerTemplate' => '<div>Footer</div>',
    ]);

    expect($footerOptions)->not->toHaveKey('headerTemplate');
});

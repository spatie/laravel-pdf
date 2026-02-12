<?php

use Spatie\LaravelPdf\Drivers\WeasyPrintDriver;
use Spatie\LaravelPdf\PdfOptions;

it('injects margin css before closing head tag', function () {
    $driver = new WeasyPrintDriver;

    $options = new PdfOptions;
    $options->margins = [
        'top' => 10,
        'right' => 20,
        'bottom' => 30,
        'left' => 40,
        'unit' => 'mm',
    ];

    $result = invade($driver)->prepareOptions($options)['stylesheet'];

    expect($result)->toStartWith('@page {');
    expect($result)->toContain('margin: 10mm 20mm 30mm 40mm;');
});

it('adds margin css stylesheet', function () {
    $driver = new WeasyPrintDriver;

    $options = new PdfOptions;
    $options->margins = [
        'top' => 5,
        'right' => 5,
        'bottom' => 5,
        'left' => 5,
        'unit' => 'cm',
    ];

    $result = invade($driver)->prepareOptions($options)['stylesheet'];

    expect($result)->toContain('margin: 5cm 5cm 5cm 5cm;');
});

it('merges header and footer into body', function () {
    $driver = new WeasyPrintDriver;

    $html = '<html><body><p>Content</p></body></html>';
    $headerHtml = '<div>Header</div>';
    $footerHtml = '<div>Footer</div>';

    $result = invade($driver)->mergeHeaderFooter($html, $headerHtml, $footerHtml);

    expect($result)->toContain('<body><div class="pdf-header"><div>Header</div></div><div class="pdf-footer"><div>Footer</div></div><p>');
});

it('merges header only when no footer provided', function () {
    $driver = new WeasyPrintDriver;

    $html = '<html><body><p>Content</p></body></html>';

    $result = invade($driver)->mergeHeaderFooter($html, '<div>Header</div>', null);

    expect($result)->toContain('<div class="pdf-header"><div>Header</div></div>');
    expect($result)->not->toContain('pdf-footer');
});

it('merges footer only when no header provided', function () {
    $driver = new WeasyPrintDriver;

    $html = '<html><body><p>Content</p></body></html>';

    $result = invade($driver)->mergeHeaderFooter($html, null, '<div>Footer</div>');

    expect($result)->not->toContain('pdf-header');
    expect($result)->toContain('<body><div class="pdf-footer"><div>Footer</div></div>');
});

it('prepends and appends when no body tag exists', function () {
    $driver = new WeasyPrintDriver;

    $html = '<p>Content</p>';

    $result = invade($driver)->mergeHeaderFooter($html, '<div>Header</div>', '<div>Footer</div>');

    expect($result)->toStartWith('<div class="pdf-header"><div>Header</div></div><div class="pdf-footer"><div>Footer</div></div>');
});

it('does not modify html when no header or footer', function () {
    $driver = new WeasyPrintDriver;

    $html = '<p>Content</p>';

    $result = invade($driver)->mergeHeaderFooter($html, null, null);

    expect($result)->toBe($html);
});

it('converts paper size to size correctly', function ($paperSize, $expected) {
    $driver = new WeasyPrintDriver;

    $options = new PdfOptions;
    $options->paperSize = $paperSize;

    $result = invade($driver)->prepareOptions($options)['stylesheet'];

    expect($result)->toContain($expected);
})->with([
    [['width' => 1, 'height' => 2, 'unit' => 'in',], 'size: 1in 2in;'],
    [['width' => 10, 'height' => 21, 'unit' => 'mm',], 'size: 10mm 21mm;'],
    [['width' => 1, 'height' => 2, 'unit' => 'cm',], 'size: 1cm 2cm;'],
    [['width' => 72, 'height' => 144, 'unit' => 'pt',], 'size: 72pt 144pt;'],
    [['width' => 1, 'height' => 2, 'unit' => 'px',], 'size: 1px 2px;'],
    [['width' => 1, 'height' => 2], '1mm 2mm'],
]);

it('converts format size to size correctly', function ($format, $expected) {
    $driver = new WeasyPrintDriver;

    $options = new PdfOptions;
    $options->format = $format;

    $result = invade($driver)->prepareOptions($options)['stylesheet'];

    expect($result)->toContain($expected);
})->with([
   ['A4', 'size: a4;'],
   ['B5', 'size: b5;']
]);

it('respects binary config', function () {
    $driver = new WeasyPrintDriver(['binary' => '/path/to/weasyprint']);

    /** @var \Pontedilana\PhpWeasyPrint\Pdf $weasyPrint */
    $weasyPrint = invade($driver)->buildWeasyPrint();

    expect($weasyPrint->getBinary())->toBe('/path/to/weasyprint');
});

it('respects timeout config', function () {
    $driver = new WeasyPrintDriver(['timeout' => 684]);

    $options = invade($driver)->buildWeasyPrint()->getOptions();

    expect($options['timeout'])->toBe(684);
});

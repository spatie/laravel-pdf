<?php

use Spatie\LaravelPdf\Drivers\DomPdfDriver;
use Spatie\LaravelPdf\PdfOptions;

it('injects margin css before closing head tag', function () {
    $driver = new DomPdfDriver;

    $options = new PdfOptions;
    $options->margins = [
        'top' => 10,
        'right' => 20,
        'bottom' => 30,
        'left' => 40,
        'unit' => 'mm',
    ];

    $html = '<html><head><title>Test</title></head><body>Content</body></html>';

    $result = invade($driver)->injectMarginCss($html, $options);

    expect($result)->toContain('@page { margin: 10mm 20mm 30mm 40mm; }');
    expect($result)->toContain('<style>@page { margin: 10mm 20mm 30mm 40mm; }</style></head>');
});

it('prepends margin css when no head tag exists', function () {
    $driver = new DomPdfDriver;

    $options = new PdfOptions;
    $options->margins = [
        'top' => 5,
        'right' => 5,
        'bottom' => 5,
        'left' => 5,
        'unit' => 'cm',
    ];

    $html = '<h1>No head tag</h1>';

    $result = invade($driver)->injectMarginCss($html, $options);

    expect($result)->toStartWith('<style>@page { margin: 5cm 5cm 5cm 5cm; }</style>');
});

it('merges header and footer into body', function () {
    $driver = new DomPdfDriver;

    $html = '<html><body><p>Content</p></body></html>';
    $headerHtml = '<div>Header</div>';
    $footerHtml = '<div>Footer</div>';

    $result = invade($driver)->mergeHeaderFooter($html, $headerHtml, $footerHtml);

    expect($result)->toContain('<body><div class="pdf-header"><div>Header</div></div>');
    expect($result)->toContain('<div class="pdf-footer"><div>Footer</div></div></body>');
});

it('merges header only when no footer provided', function () {
    $driver = new DomPdfDriver;

    $html = '<html><body><p>Content</p></body></html>';

    $result = invade($driver)->mergeHeaderFooter($html, '<div>Header</div>', null);

    expect($result)->toContain('<div class="pdf-header"><div>Header</div></div>');
    expect($result)->not->toContain('pdf-footer');
});

it('merges footer only when no header provided', function () {
    $driver = new DomPdfDriver;

    $html = '<html><body><p>Content</p></body></html>';

    $result = invade($driver)->mergeHeaderFooter($html, null, '<div>Footer</div>');

    expect($result)->not->toContain('pdf-header');
    expect($result)->toContain('<div class="pdf-footer"><div>Footer</div></div></body>');
});

it('prepends and appends when no body tag exists', function () {
    $driver = new DomPdfDriver;

    $html = '<p>Content</p>';

    $result = invade($driver)->mergeHeaderFooter($html, '<div>Header</div>', '<div>Footer</div>');

    expect($result)->toStartWith('<div class="pdf-header"><div>Header</div></div>');
    expect($result)->toEndWith('<div class="pdf-footer"><div>Footer</div></div>');
});

it('does not modify html when no header or footer', function () {
    $driver = new DomPdfDriver;

    $html = '<p>Content</p>';

    $result = invade($driver)->mergeHeaderFooter($html, null, null);

    expect($result)->toBe($html);
});

it('converts units to points correctly', function () {
    $driver = new DomPdfDriver;

    expect(invade($driver)->toPoints(1, 'in'))->toBe(72.0);
    expect(invade($driver)->toPoints(10, 'mm'))->toBe(28.3465);
    expect(invade($driver)->toPoints(1, 'cm'))->toBe(28.3465);
    expect(invade($driver)->toPoints(72, 'pt'))->toBe(72.0);
    expect(invade($driver)->toPoints(1, 'px'))->toBe(0.75);
});

it('respects is_remote_enabled config', function () {
    $driver = new DomPdfDriver(['is_remote_enabled' => true]);

    $options = invade($driver)->buildOptions();

    expect($options->getIsRemoteEnabled())->toBeTrue();
});

it('respects chroot config', function () {
    $driver = new DomPdfDriver(['chroot' => '/custom/path']);

    $options = invade($driver)->buildOptions();

    expect($options->getChroot())->toContain('/custom/path');
});

<?php

use Spatie\LaravelPdf\Drivers\BrowsershotDriver;
use Spatie\LaravelPdf\Drivers\SupportsReadiness;
use Spatie\LaravelPdf\PdfOptions;

it('supports readiness', function () {
    expect(new BrowsershotDriver)->toBeInstanceOf(SupportsReadiness::class);
});

it('configures browsershot to wait for the readiness expression', function () {
    $driver = new BrowsershotDriver;

    $options = new PdfOptions;
    $options->waitForReady = 'window.pdfReady === true';
    $options->waitForReadyTimeout = 5000;

    $browsershot = invade($driver)->buildBrowsershot('<p>hi</p>', null, null, $options);

    expect(invade($browsershot)->additionalOptions['function'])->toBe('window.pdfReady === true');
    expect(invade($browsershot)->additionalOptions['functionTimeout'])->toBe(5000);
});

it('does not configure a wait function when readiness is not requested', function () {
    $driver = new BrowsershotDriver;

    $browsershot = invade($driver)->buildBrowsershot('<p>hi</p>', null, null, new PdfOptions);

    expect(invade($browsershot)->additionalOptions)->not->toHaveKey('function');
});

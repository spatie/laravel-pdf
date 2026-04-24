<?php

use Illuminate\Support\Env;
use Illuminate\Support\Facades\Config;
use Spatie\Browsershot\Browsershot;
use Spatie\LaravelPdf\Drivers\BrowsershotDriver;
use Spatie\LaravelPdf\PdfOptions;

beforeEach(function () {
    Config::set('laravel-pdf', ['driver' => 'browsershot']);
});

it('applies binary path configurations', function (string $configKey, string $property, string $testPath) {
    Config::set("laravel-pdf.browsershot.{$configKey}", $testPath);

    $driver = new BrowsershotDriver(config('laravel-pdf.browsershot'));
    $browsershot = invade($driver)->buildBrowsershot('test', null, null, new PdfOptions);

    expect(invade($browsershot)->{$property})->toBe($testPath);
})->with([
    ['node_binary', 'nodeBinary', '/test/node'],
    ['npm_binary', 'npmBinary', '/test/npm'],
    ['include_path', 'includePath', '/test/include'],
    ['node_modules_path', 'nodeModulePath', '/test/modules'],
    ['bin_path', 'binPath', '/test/bin'],
    ['temp_path', 'tempPath', '/test/temp'],
]);

it('applies chrome path configuration', function () {
    Config::set('laravel-pdf.browsershot.chrome_path', '/test/chrome');

    $driver = new BrowsershotDriver(config('laravel-pdf.browsershot'));
    $browsershot = invade($driver)->buildBrowsershot('test', null, null, new PdfOptions);

    expect(getBrowsershotOption($browsershot, 'executablePath'))->toBe('/test/chrome');
});

it('does not apply configuration when values are null or empty', function () {
    Config::set('laravel-pdf.browsershot.node_binary', null);
    Config::set('laravel-pdf.browsershot.chrome_path', '');

    $driver = new BrowsershotDriver(config('laravel-pdf.browsershot'));
    $browsershot = invade($driver)->buildBrowsershot('test', null, null, new PdfOptions);

    expect(invade($browsershot)->nodeBinary)->toBeNull();
    expect(getBrowsershotOption($browsershot, 'executablePath'))->toBeNull();
});

it('applies write options to file when enabled', function () {
    Config::set('laravel-pdf.browsershot.write_options_to_file', true);

    $driver = new BrowsershotDriver(config('laravel-pdf.browsershot'));
    $browsershot = invade($driver)->buildBrowsershot('test', null, null, new PdfOptions);

    expect(invade($browsershot)->writeOptionsToFile)->toBeTrue();
});

it('does not apply write options to file when disabled', function () {
    Config::set('laravel-pdf.browsershot.write_options_to_file', false);

    $driver = new BrowsershotDriver(config('laravel-pdf.browsershot'));
    $browsershot = invade($driver)->buildBrowsershot('test', null, null, new PdfOptions);

    expect(invade($browsershot)->writeOptionsToFile)->toBeFalse();
});

it('applies configuration defaults first', function () {
    Config::set('laravel-pdf.browsershot.chrome_path', '/config/chrome');

    $driver = new BrowsershotDriver(config('laravel-pdf.browsershot'));
    $browsershot = invade($driver)->buildBrowsershot('test', null, null, new PdfOptions);

    expect(getBrowsershotOption($browsershot, 'executablePath'))->toBe('/config/chrome');
});

it('allows withBrowsershot callback to override configuration defaults', function () {
    Config::set('laravel-pdf.browsershot.chrome_path', '/config/chrome');

    $driver = new BrowsershotDriver(config('laravel-pdf.browsershot'));
    $driver->customizeBrowsershot(function (Browsershot $browsershot) {
        $browsershot->setChromePath('/override/chrome');
    });

    $browsershot = invade($driver)->buildBrowsershot('test', null, null, new PdfOptions);

    expect(getBrowsershotOption($browsershot, 'executablePath'))->toBe('/override/chrome');
});

it('applies multiple configuration options simultaneously', function () {
    Config::set('laravel-pdf.browsershot', [
        'chrome_path' => '/test/chrome',
        'node_binary' => '/test/node',
        'write_options_to_file' => true,
    ]);

    $driver = new BrowsershotDriver(config('laravel-pdf.browsershot'));
    $browsershot = invade($driver)->buildBrowsershot('test', null, null, new PdfOptions);

    expect(getBrowsershotOption($browsershot, 'executablePath'))->toBe('/test/chrome');
    expect(invade($browsershot)->nodeBinary)->toBe('/test/node');
    expect(invade($browsershot)->writeOptionsToFile)->toBeTrue();
});

it('applies scale option to browsershot', function () {
    $driver = new BrowsershotDriver;

    $options = new PdfOptions;
    $options->scale = 0.75;

    $browsershot = invade($driver)->buildBrowsershot('test', null, null, $options);

    expect(invade($browsershot)->scale)->toBe(0.75);
});

it('applies page ranges option to browsershot', function () {
    $driver = new BrowsershotDriver;

    $options = new PdfOptions;
    $options->pageRanges = '1-3, 5';

    $browsershot = invade($driver)->buildBrowsershot('test', null, null, $options);

    expect(getBrowsershotOption($browsershot, 'pageRanges'))->toBe('1-3, 5');
});

it('applies tagged pdf option to browsershot', function () {
    $driver = new BrowsershotDriver;

    $options = new PdfOptions;
    $options->tagged = true;

    $browsershot = invade($driver)->buildBrowsershot('test', null, null, $options);

    expect(invade($browsershot)->taggedPdf)->toBeTrue();
});

it('splits LARAVEL_PDF_FALLBACK_DRIVERS env into the drivers array', function () {
    Env::getRepository()->set('LARAVEL_PDF_FALLBACK_DRIVERS', 'dompdf,chrome,gotenberg');

    try {
        $config = require __DIR__.'/../../config/laravel-pdf.php';

        expect(array_values($config['fallback']['drivers']))->toBe(['dompdf', 'chrome', 'gotenberg']);
    } finally {
        Env::getRepository()->clear('LARAVEL_PDF_FALLBACK_DRIVERS');
    }
});

it('returns an empty drivers array when LARAVEL_PDF_FALLBACK_DRIVERS env is unset', function () {
    Env::getRepository()->clear('LARAVEL_PDF_FALLBACK_DRIVERS');

    $config = require __DIR__.'/../../config/laravel-pdf.php';

    expect($config['fallback']['drivers'])->toBe([]);
});

it('splits a single LARAVEL_PDF_FALLBACK_DRIVERS driver', function () {
    Env::getRepository()->set('LARAVEL_PDF_FALLBACK_DRIVERS', 'dompdf');

    try {
        $config = require __DIR__.'/../../config/laravel-pdf.php';

        expect(array_values($config['fallback']['drivers']))->toBe(['dompdf']);
    } finally {
        Env::getRepository()->clear('LARAVEL_PDF_FALLBACK_DRIVERS');
    }
});

function getBrowsershotOption(object $browsershot, string $key): mixed
{
    $options = invade($browsershot)->additionalOptions;

    return $options[$key] ?? null;
}

<?php

use Illuminate\Support\Facades\Config;
use Spatie\Browsershot\Browsershot;
use Spatie\LaravelPdf\Facades\Pdf;

beforeEach(function () {
    Config::set('laravel-pdf', []);
});

it('applies binary path configurations', function (string $configKey, string $property, string $testPath) {
    Config::set("laravel-pdf.browsershot.{$configKey}", $testPath);

    $browsershot = Pdf::view('test')->getBrowsershot();

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

    $browsershot = Pdf::view('test')->getBrowsershot();

    expect(getBrowsershotOption($browsershot, 'executablePath'))->toBe('/test/chrome');
});

it('does not apply configuration when values are null or empty', function () {
    Config::set('laravel-pdf.browsershot.node_binary', null);
    Config::set('laravel-pdf.browsershot.chrome_path', '');

    $browsershot = Pdf::view('test')->getBrowsershot();

    expect(invade($browsershot)->nodeBinary)->toBeNull();
    expect(getBrowsershotOption($browsershot, 'executablePath'))->toBeNull();
});

it('applies write options to file when enabled', function () {
    Config::set('laravel-pdf.browsershot.write_options_to_file', true);

    $browsershot = Pdf::view('test')->getBrowsershot();

    expect(invade($browsershot)->writeOptionsToFile)->toBeTrue();
});

it('does not apply write options to file when disabled', function () {
    Config::set('laravel-pdf.browsershot.write_options_to_file', false);

    $browsershot = Pdf::view('test')->getBrowsershot();

    expect(invade($browsershot)->writeOptionsToFile)->toBeFalse();
});

it('applies configuration defaults first', function () {
    Config::set('laravel-pdf.browsershot.chrome_path', '/config/chrome');

    $browsershot = Pdf::view('test')->getBrowsershot();

    expect(getBrowsershotOption($browsershot, 'executablePath'))->toBe('/config/chrome');
});

it('allows withBrowsershot callback to override configuration defaults', function () {
    Config::set('laravel-pdf.browsershot.chrome_path', '/config/chrome');

    $browsershot = Pdf::view('test')
        ->withBrowsershot(function (Browsershot $browsershot) {
            $browsershot->setChromePath('/override/chrome');
        })
        ->getBrowsershot();

    expect(getBrowsershotOption($browsershot, 'executablePath'))->toBe('/override/chrome');
});

it('applies multiple configuration options simultaneously', function () {
    Config::set('laravel-pdf.browsershot', [
        'chrome_path' => '/test/chrome',
        'node_binary' => '/test/node',
        'write_options_to_file' => true,
    ]);

    $browsershot = Pdf::view('test')->getBrowsershot();

    expect(getBrowsershotOption($browsershot, 'executablePath'))->toBe('/test/chrome');
    expect(invade($browsershot)->nodeBinary)->toBe('/test/node');
    expect(invade($browsershot)->writeOptionsToFile)->toBeTrue();
});

function getBrowsershotOption(object $browsershot, string $key): mixed
{
    $options = invade($browsershot)->additionalOptions;

    return $options[$key] ?? null;
}

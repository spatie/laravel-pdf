<?php

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Spatie\LaravelPdf\Drivers\FallbackDriver;
use Spatie\LaravelPdf\Exceptions\CouldNotGeneratePdf;
use Spatie\LaravelPdf\Facades\Pdf;

beforeEach(function () {
    forgetPdfDriverInstances();

    Config::set('laravel-pdf.fallback', [
        'drivers' => [],
        'only_on_exceptions' => [],
        'except_exceptions' => [],
        'health_cache' => [
            'ttl' => 0,
            'key_prefix' => 'laravel_pdf_driver_health_',
            'store' => null,
        ],
    ]);
});

it('uses fluent fallback() to build a FallbackDriver chain', function () {
    bindFakeDriver('cloudflare', fakePdfDriver(new RuntimeException('cf-down')));
    $dompdf = fakePdfDriver('dompdf-output');
    bindFakeDriver('dompdf', $dompdf);

    $path = getTempPath('fluent-fallback.pdf');

    Pdf::html('<h1>Hi</h1>')
        ->driver('cloudflare')
        ->fallback('dompdf')
        ->save($path);

    expect(file_get_contents($path))->toBe('dompdf-output');
    expect($dompdf->saveCalls)->toBe(1);
});

it('respects global fallback_drivers config without using the fluent method', function () {
    Config::set('laravel-pdf.driver', 'cloudflare');
    Config::set('laravel-pdf.fallback.drivers', ['dompdf']);

    bindFakeDriver('cloudflare', fakePdfDriver(new RuntimeException('cf-down')));
    $dompdf = fakePdfDriver('dompdf-output');
    bindFakeDriver('dompdf', $dompdf);

    $path = getTempPath('global-fallback.pdf');

    Pdf::html('<h1>Hi</h1>')->save($path);

    expect(file_get_contents($path))->toBe('dompdf-output');
});

it('fluent fallback() takes precedence over global fallback_drivers config', function () {
    Config::set('laravel-pdf.fallback.drivers', ['gotenberg']);

    bindFakeDriver('cloudflare', fakePdfDriver(new RuntimeException('cf-down')));
    bindFakeDriver('gotenberg', fakePdfDriver(new RuntimeException('gotenberg-down')));
    $dompdf = fakePdfDriver('chosen');
    bindFakeDriver('dompdf', $dompdf);

    $path = getTempPath('precedence-fallback.pdf');

    Pdf::html('<h1>Hi</h1>')
        ->driver('cloudflare')
        ->fallback('dompdf')
        ->save($path);

    expect(file_get_contents($path))->toBe('chosen');
    expect($dompdf->saveCalls)->toBe(1);
});

it('returns the original driver instance when no fallback is configured', function () {
    Config::set('laravel-pdf.driver', 'dompdf');

    $dompdf = fakePdfDriver('only-dompdf');
    bindFakeDriver('dompdf', $dompdf);

    $path = getTempPath('no-fallback.pdf');

    Pdf::html('<h1>Hi</h1>')->save($path);

    expect(file_get_contents($path))->toBe('only-dompdf');
});

it('accepts both string and array in fallback()', function () {
    bindFakeDriver('cloudflare', fakePdfDriver(new RuntimeException('cf-down')));
    bindFakeDriver('chrome', fakePdfDriver(new RuntimeException('chrome-down')));
    $dompdf = fakePdfDriver('final');
    bindFakeDriver('dompdf', $dompdf);

    $path = getTempPath('array-fallback.pdf');

    Pdf::html('<h1>Hi</h1>')
        ->driver('cloudflare')
        ->fallback(['chrome', 'dompdf'])
        ->save($path);

    expect(file_get_contents($path))->toBe('final');
});

it('passes only_on_exceptions config through to FallbackDriver', function () {
    Config::set('laravel-pdf.driver', 'cloudflare');
    Config::set('laravel-pdf.fallback.drivers', ['dompdf']);
    Config::set('laravel-pdf.fallback.only_on_exceptions', [LogicException::class]);

    bindFakeDriver('cloudflare', fakePdfDriver(new RuntimeException('not-allowed')));
    bindFakeDriver('dompdf', fakePdfDriver('should-not-be-used'));

    $path = getTempPath('only-on.pdf');

    expect(fn () => Pdf::html('<h1>Hi</h1>')->save($path))
        ->toThrow(RuntimeException::class, 'not-allowed');
});

it('passes except_exceptions config through to FallbackDriver', function () {
    Config::set('laravel-pdf.driver', 'cloudflare');
    Config::set('laravel-pdf.fallback.drivers', ['dompdf']);
    Config::set('laravel-pdf.fallback.except_exceptions', [LogicException::class]);

    bindFakeDriver('cloudflare', fakePdfDriver(new LogicException('blocked')));
    bindFakeDriver('dompdf', fakePdfDriver('should-not-be-used'));

    $path = getTempPath('except.pdf');

    expect(fn () => Pdf::html('<h1>Hi</h1>')->save($path))
        ->toThrow(LogicException::class, 'blocked');
});

it('passes health_cache.ttl through to FallbackDriver and persists health state', function () {
    Cache::store('array')->flush();

    Config::set('cache.default', 'array');
    Config::set('laravel-pdf.driver', 'cloudflare');
    Config::set('laravel-pdf.fallback.drivers', ['dompdf']);
    Config::set('laravel-pdf.fallback.health_cache.ttl', 600);

    bindFakeDriver('cloudflare', fakePdfDriver(new RuntimeException('cf-down')));
    bindFakeDriver('dompdf', fakePdfDriver('ok'));

    Pdf::html('<h1>Hi</h1>')->save(getTempPath('health-1.pdf'));

    expect(Cache::store('array')->has('laravel_pdf_driver_health_cloudflare'))->toBeTrue();
});

it('uses the configured key_prefix from fallback config', function () {
    Cache::store('array')->flush();

    Config::set('cache.default', 'array');
    Config::set('laravel-pdf.driver', 'cloudflare');
    Config::set('laravel-pdf.fallback.drivers', ['dompdf']);
    Config::set('laravel-pdf.fallback.health_cache.ttl', 600);
    Config::set('laravel-pdf.fallback.health_cache.key_prefix', 'mytenant_pdf_');

    bindFakeDriver('cloudflare', fakePdfDriver(new RuntimeException('cf-down')));
    bindFakeDriver('dompdf', fakePdfDriver('ok'));

    Pdf::html('<h1>Hi</h1>')->save(getTempPath('health-2.pdf'));

    expect(Cache::store('array')->has('mytenant_pdf_cloudflare'))->toBeTrue();
});

it('throws CouldNotGeneratePdf with attempted drivers when entire chain fails', function () {
    Config::set('laravel-pdf.driver', 'cloudflare');
    Config::set('laravel-pdf.fallback.drivers', ['dompdf', 'gotenberg']);

    bindFakeDriver('cloudflare', fakePdfDriver(new RuntimeException('cf')));
    bindFakeDriver('dompdf', fakePdfDriver(new RuntimeException('dp')));
    bindFakeDriver('gotenberg', fakePdfDriver(new RuntimeException('gt')));

    $caught = null;
    try {
        Pdf::html('<h1>Hi</h1>')->save(getTempPath('all-fail.pdf'));
    } catch (CouldNotGeneratePdf $e) {
        $caught = $e;
    }

    expect($caught)->not->toBeNull();
    expect($caught->attemptedDrivers)->toBe(['cloudflare', 'dompdf', 'gotenberg']);
    expect($caught->driverExceptions)->toHaveKeys(['cloudflare', 'dompdf', 'gotenberg']);
});

it('builds a FallbackDriver instance via the fluent API', function () {
    bindFakeDriver('cloudflare', fakePdfDriver('ok'));
    bindFakeDriver('dompdf', fakePdfDriver('also-ok'));

    $builder = Pdf::html('<h1>Hi</h1>')
        ->driver('cloudflare')
        ->fallback('dompdf');

    $driver = invade($builder)->getDriver();

    expect($driver)->toBeInstanceOf(FallbackDriver::class);
    expect($driver->getDrivers())->toHaveCount(2);
});

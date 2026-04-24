<?php

use Illuminate\Support\Facades\Config;

beforeEach(fn () => forgetPdfDriverInstances());

it('returns success when the primary driver is healthy and no fallback is configured', function () {
    Config::set('laravel-pdf.driver', 'dompdf');
    Config::set('laravel-pdf.fallback.drivers', []);

    $dompdf = fakePdfDriver('pdf-content');
    bindFakeDriver('dompdf', $dompdf);

    $this->artisan('pdf:health')
        ->expectsOutputToContain('All 1 driver(s) are healthy.')
        ->assertSuccessful();

    expect($dompdf->generateCalls)->toBe(1);
});

it('returns failure when the primary driver throws', function () {
    Config::set('laravel-pdf.driver', 'cloudflare');
    Config::set('laravel-pdf.fallback.drivers', []);

    $cloudflare = fakePdfDriver(new RuntimeException('credentials missing'));
    bindFakeDriver('cloudflare', $cloudflare);

    $this->artisan('pdf:health')
        ->expectsOutputToContain('1 of 1 driver(s) failed.')
        ->assertFailed();

    expect($cloudflare->generateCalls)->toBe(1);
});

it('checks the primary and every fallback driver', function () {
    Config::set('laravel-pdf.driver', 'cloudflare');
    Config::set('laravel-pdf.fallback.drivers', ['chrome', 'dompdf']);

    $cloudflare = fakePdfDriver('a');
    $chrome = fakePdfDriver('b');
    $dompdf = fakePdfDriver('c');

    bindFakeDriver('cloudflare', $cloudflare);
    bindFakeDriver('chrome', $chrome);
    bindFakeDriver('dompdf', $dompdf);

    $this->artisan('pdf:health')
        ->expectsOutputToContain('All 3 driver(s) are healthy.')
        ->assertSuccessful();

    expect($cloudflare->generateCalls)->toBe(1);
    expect($chrome->generateCalls)->toBe(1);
    expect($dompdf->generateCalls)->toBe(1);
});

it('reports a healthy primary alongside a failing fallback and exits with failure', function () {
    Config::set('laravel-pdf.driver', 'cloudflare');
    Config::set('laravel-pdf.fallback.drivers', ['dompdf']);

    $cloudflare = fakePdfDriver('ok');
    $dompdf = fakePdfDriver(new RuntimeException('dompdf-broken'));

    bindFakeDriver('cloudflare', $cloudflare);
    bindFakeDriver('dompdf', $dompdf);

    $this->artisan('pdf:health')
        ->expectsOutputToContain('1 of 2 driver(s) failed.')
        ->assertFailed();

    expect($cloudflare->generateCalls)->toBe(1);
    expect($dompdf->generateCalls)->toBe(1);
});

it('deduplicates a driver appearing both as primary and as fallback', function () {
    Config::set('laravel-pdf.driver', 'dompdf');
    Config::set('laravel-pdf.fallback.drivers', ['dompdf', 'cloudflare']);

    $dompdf = fakePdfDriver('a');
    $cloudflare = fakePdfDriver('b');

    bindFakeDriver('dompdf', $dompdf);
    bindFakeDriver('cloudflare', $cloudflare);

    $this->artisan('pdf:health')
        ->expectsOutputToContain('All 2 driver(s) are healthy.')
        ->assertSuccessful();

    expect($dompdf->generateCalls)->toBe(1);
    expect($cloudflare->generateCalls)->toBe(1);
});

it('shows the failure detail line for a broken driver', function () {
    Config::set('laravel-pdf.driver', 'cloudflare');
    Config::set('laravel-pdf.fallback.drivers', []);

    bindFakeDriver('cloudflare', fakePdfDriver(new RuntimeException('upstream-down')));

    $this->artisan('pdf:health')
        ->expectsOutputToContain('upstream-down')
        ->assertFailed();
});

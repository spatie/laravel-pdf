<?php

use Illuminate\Support\Facades\Cache;
use Spatie\LaravelPdf\Caching\DefaultPdfCache;
use Spatie\LaravelPdf\Caching\PdfCache;

beforeEach(function () {
    Cache::flush();
});

it('is the default binding for the PdfCache contract', function () {
    expect(app(PdfCache::class))->toBeInstanceOf(DefaultPdfCache::class);
});

it('generates content on a miss and reuses it on a hit', function () {
    $cache = new DefaultPdfCache;
    $calls = 0;

    $generate = function () use (&$calls) {
        $calls++;

        return "%PDF-{$calls}";
    };

    $first = $cache->remember('fingerprint-a', null, null, $generate);
    $second = $cache->remember('fingerprint-a', null, null, $generate);

    expect($first)->toBe('%PDF-1');
    expect($second)->toBe('%PDF-1');
    expect($calls)->toBe(1);
});

it('uses a separate entry for a different fingerprint', function () {
    $cache = new DefaultPdfCache;
    $calls = 0;
    $generate = function () use (&$calls) {
        $calls++;

        return "%PDF-{$calls}";
    };

    $cache->remember('fingerprint-a', null, null, $generate);
    $cache->remember('fingerprint-b', null, null, $generate);

    expect($calls)->toBe(2);
});

it('shares one entry across fingerprints when a custom key is given', function () {
    $cache = new DefaultPdfCache;
    $calls = 0;
    $generate = function () use (&$calls) {
        $calls++;

        return "%PDF-{$calls}";
    };

    $cache->remember('fingerprint-a', 'shared-key', null, $generate);
    $cache->remember('fingerprint-b', 'shared-key', null, $generate);

    expect($calls)->toBe(1);
});

it('round-trips binary content without corruption', function () {
    $cache = new DefaultPdfCache;
    $binary = random_bytes(2048);

    $result = $cache->remember('binary', null, null, fn () => $binary);

    expect($result)->toBe($binary);
});

it('stores the content under the configured prefix', function () {
    config()->set('laravel-pdf.cache.prefix', 'custom-prefix');

    $cache = new DefaultPdfCache;
    $cache->remember('fingerprint-a', null, null, fn () => '%PDF');

    expect(Cache::has('custom-prefix:'.sha1('fingerprint-a')))->toBeTrue();
});

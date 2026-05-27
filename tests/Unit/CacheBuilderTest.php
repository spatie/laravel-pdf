<?php

use Illuminate\Support\Facades\Cache;
use Spatie\LaravelPdf\Caching\PdfCache;
use Spatie\LaravelPdf\Exceptions\CouldNotGeneratePdf;
use Spatie\LaravelPdf\Facades\Pdf;
use Spatie\LaravelPdf\PdfOptions;
use Spatie\LaravelPdf\Tests\TestSupport\FakeDriver;

use function Illuminate\Support\hours;

beforeEach(function () {
    Cache::flush();
});

it('does not cache by default', function () {
    $driver = new FakeDriver;

    Pdf::html('<p>hi</p>')->setDriver($driver)->base64();
    Pdf::html('<p>hi</p>')->setDriver($driver)->base64();

    expect($driver->generateCount)->toBe(2);
});

it('caches generated content across renders', function () {
    $driver = new FakeDriver;

    $first = Pdf::html('<p>hi</p>')->setDriver($driver)->cache()->base64();
    $second = Pdf::html('<p>hi</p>')->setDriver($driver)->cache()->base64();

    expect($driver->generateCount)->toBe(1);
    expect($first)->toBe($second);
});

it('uses a separate cache entry when the html differs', function () {
    $driver = new FakeDriver;

    Pdf::html('<p>one</p>')->setDriver($driver)->cache()->base64();
    Pdf::html('<p>two</p>')->setDriver($driver)->cache()->base64();

    expect($driver->generateCount)->toBe(2);
});

it('shares a cache entry when a custom key is given', function () {
    $driver = new FakeDriver;

    Pdf::html('<p>one</p>')->setDriver($driver)->cache(null, 'shared')->base64();
    Pdf::html('<p>two</p>')->setDriver($driver)->cache(null, 'shared')->base64();

    expect($driver->generateCount)->toBe(1);
});

it('serves a cached pdf when saving to a path', function () {
    $driver = new FakeDriver('%PDF-cached');
    $path = getTempPath('cached.pdf');

    Pdf::html('<p>hi</p>')->setDriver($driver)->cache()->save($path);
    Pdf::html('<p>hi</p>')->setDriver($driver)->cache()->save($path);

    expect($driver->generateCount)->toBe(1);
    expect($driver->saveCount)->toBe(0);
    expect(file_get_contents($path))->toBe('%PDF-cached');
});

it('caches for a day by default', function () {
    expect(config('laravel-pdf.cache.ttl'))->toBe(60 * 60 * 24);
});

it('caches automatically when enabled in config', function () {
    config()->set('laravel-pdf.cache.automatic', true);

    $driver = new FakeDriver;

    Pdf::html('<p>hi</p>')->setDriver($driver)->base64();
    Pdf::html('<p>hi</p>')->setDriver($driver)->base64();

    expect($driver->generateCount)->toBe(1);
});

it('can opt out of automatic caching with dontCache', function () {
    config()->set('laravel-pdf.cache.automatic', true);

    $driver = new FakeDriver;

    Pdf::html('<p>hi</p>')->setDriver($driver)->dontCache()->base64();
    Pdf::html('<p>hi</p>')->setDriver($driver)->dontCache()->base64();

    expect($driver->generateCount)->toBe(2);
});

it('throws when caching a pdf customized with a browsershot closure', function () {
    Pdf::html('<p>hi</p>')
        ->setDriver(new FakeDriver)
        ->withBrowsershot(fn () => null)
        ->cache()
        ->base64();
})->throws(CouldNotGeneratePdf::class);

it('allows caching a browsershot-customized pdf when an explicit key is given', function () {
    $content = Pdf::html('<p>hi</p>')
        ->setDriver(new FakeDriver)
        ->withBrowsershot(fn () => null)
        ->cache(key: 'explicit')
        ->base64();

    expect(base64_decode($content))->toBe('%PDF-fake');
});

it('uses the driver name in the cache fingerprint', function () {
    $first = invade(Pdf::html('<p>hi</p>')->driver('browsershot'))
        ->cacheFingerprint('<p>hi</p>', null, null, new PdfOptions);

    $second = invade(Pdf::html('<p>hi</p>')->driver('chrome'))
        ->cacheFingerprint('<p>hi</p>', null, null, new PdfOptions);

    expect($first)->not->toBe($second);
});

it('accepts a carbon interval as a lifetime', function () {
    $driver = new FakeDriver;

    Pdf::html('<p>hi</p>')->setDriver($driver)->cache(hours(2))->base64();
    Pdf::html('<p>hi</p>')->setDriver($driver)->cache(hours(2))->base64();

    expect($driver->generateCount)->toBe(1);
});

it('accepts a date time as a lifetime', function () {
    $driver = new FakeDriver;

    Pdf::html('<p>hi</p>')->setDriver($driver)->cache(now()->addHour())->base64();
    Pdf::html('<p>hi</p>')->setDriver($driver)->cache(now()->addHour())->base64();

    expect($driver->generateCount)->toBe(1);
});

it('honors an expired lifetime by regenerating', function () {
    $driver = new FakeDriver;

    Pdf::html('<p>hi</p>')->setDriver($driver)->cache(now()->subMinute())->base64();
    Pdf::html('<p>hi</p>')->setDriver($driver)->cache(now()->subMinute())->base64();

    expect($driver->generateCount)->toBe(2);
});

it('uses the cache implementation bound in the container', function () {
    app()->instance(PdfCache::class, new class implements PdfCache
    {
        public function remember(string $fingerprint, ?string $key, DateTimeInterface|DateInterval|int|null $ttl, Closure $generate): string
        {
            return 'overridden-content';
        }
    });

    $driver = new FakeDriver;

    $content = Pdf::html('<p>hi</p>')->setDriver($driver)->cache()->base64();

    expect(base64_decode($content))->toBe('overridden-content');
    expect($driver->generateCount)->toBe(0);
});

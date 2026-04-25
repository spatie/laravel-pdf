<?php

use Illuminate\Contracts\Cache\Repository as CacheRepositoryContract;
use Illuminate\Contracts\Debug\ExceptionHandler;
use Illuminate\Support\Facades\Cache;
use Spatie\LaravelPdf\Drivers\FallbackDriver;
use Spatie\LaravelPdf\Exceptions\CouldNotGeneratePdf;
use Spatie\LaravelPdf\PdfOptions;

function nullExceptionHandler(): ExceptionHandler
{
    return new class implements ExceptionHandler
    {
        public array $reported = [];

        public function report(Throwable $e)
        {
            $this->reported[] = $e;
        }

        public function shouldReport(Throwable $e)
        {
            return true;
        }

        public function render($request, Throwable $e)
        {
            return null;
        }

        public function renderForConsole($output, Throwable $e) {}
    };
}

function makeFallback(
    array $driversAndNames,
    ExceptionHandler $handler,
    array $onlyOn = [],
    array $except = [],
    ?CacheRepositoryContract $cache = null,
    int $ttl = 0,
    string $prefix = 'laravel_pdf_driver_health_',
): FallbackDriver {
    return new FallbackDriver(
        drivers: $driversAndNames,
        exceptionHandler: $handler,
        onlyOnExceptions: $onlyOn,
        exceptExceptions: $except,
        cacheRepository: $cache,
        healthCacheTtl: $ttl,
        healthCacheKeyPrefix: $prefix,
    );
}

it('uses the primary driver when it succeeds', function () {
    $primary = fakePdfDriver('primary-output');
    $fallback = fakePdfDriver('fallback-output');

    $driver = makeFallback(
        ['primary' => $primary, 'fallback' => $fallback],
        nullExceptionHandler(),
    );

    $result = $driver->generatePdf('<h1>x</h1>', null, null, new PdfOptions);

    expect($result)->toBe('primary-output');
    expect($primary->generateCalls)->toBe(1);
    expect($fallback->generateCalls)->toBe(0);
});

it('falls back to the next driver when the first throws', function () {
    $primary = fakePdfDriver(new RuntimeException('boom'));
    $fallback = fakePdfDriver('fallback-output');

    $driver = makeFallback(
        ['primary' => $primary, 'fallback' => $fallback],
        nullExceptionHandler(),
    );

    $result = $driver->generatePdf('<h1>x</h1>', null, null, new PdfOptions);

    expect($result)->toBe('fallback-output');
    expect($primary->generateCalls)->toBe(1);
    expect($fallback->generateCalls)->toBe(1);
});

it('falls back through multiple drivers until one succeeds', function () {
    $a = fakePdfDriver(new RuntimeException('a-fail'));
    $b = fakePdfDriver(new RuntimeException('b-fail'));
    $c = fakePdfDriver('c-ok');

    $driver = makeFallback(
        ['a' => $a, 'b' => $b, 'c' => $c],
        nullExceptionHandler(),
    );

    expect($driver->generatePdf('h', null, null, new PdfOptions))->toBe('c-ok');
});

it('throws CouldNotGeneratePdf carrying attempts when all drivers fail', function () {
    $a = fakePdfDriver(new RuntimeException('a-fail'));
    $b = fakePdfDriver(new RuntimeException('b-fail'));

    $driver = makeFallback(
        ['a' => $a, 'b' => $b],
        nullExceptionHandler(),
    );

    try {
        $driver->generatePdf('h', null, null, new PdfOptions);
        $this->fail('Expected CouldNotGeneratePdf');
    } catch (CouldNotGeneratePdf $e) {
        expect($e->attemptedDrivers)->toBe(['a', 'b']);
        expect($e->driverExceptions)->toHaveKeys(['a', 'b']);
        expect($e->driverExceptions['b']->getMessage())->toBe('b-fail');
        expect($e->getPrevious())->toBe($e->driverExceptions['b']);
    }
});

it('only_on_exceptions: does not fall back when exception is not in allowlist', function () {
    $a = fakePdfDriver(new RuntimeException('not allowed'));
    $b = fakePdfDriver('b-ok');

    $driver = makeFallback(
        ['a' => $a, 'b' => $b],
        nullExceptionHandler(),
        onlyOn: [LogicException::class],
    );

    expect(fn () => $driver->generatePdf('h', null, null, new PdfOptions))
        ->toThrow(RuntimeException::class, 'not allowed');

    expect($b->generateCalls)->toBe(0);
});

it('only_on_exceptions: falls back when exception is in allowlist', function () {
    $a = fakePdfDriver(new RuntimeException('allowed'));
    $b = fakePdfDriver('b-ok');

    $driver = makeFallback(
        ['a' => $a, 'b' => $b],
        nullExceptionHandler(),
        onlyOn: [RuntimeException::class],
    );

    expect($driver->generatePdf('h', null, null, new PdfOptions))->toBe('b-ok');
});

it('except_exceptions: does not fall back when exception is in denylist', function () {
    $a = fakePdfDriver(new LogicException('blocked'));
    $b = fakePdfDriver('b-ok');

    $driver = makeFallback(
        ['a' => $a, 'b' => $b],
        nullExceptionHandler(),
        except: [LogicException::class],
    );

    expect(fn () => $driver->generatePdf('h', null, null, new PdfOptions))
        ->toThrow(LogicException::class, 'blocked');
});

it('except_exceptions: falls back when exception is not in denylist', function () {
    $a = fakePdfDriver(new RuntimeException('not blocked'));
    $b = fakePdfDriver('b-ok');

    $driver = makeFallback(
        ['a' => $a, 'b' => $b],
        nullExceptionHandler(),
        except: [LogicException::class],
    );

    expect($driver->generatePdf('h', null, null, new PdfOptions))->toBe('b-ok');
});

it('only_on_exceptions takes precedence when both are configured', function () {
    $a = fakePdfDriver(new RuntimeException('runtime'));
    $b = fakePdfDriver('b-ok');

    $driver = makeFallback(
        ['a' => $a, 'b' => $b],
        nullExceptionHandler(),
        onlyOn: [RuntimeException::class],
        except: [RuntimeException::class],
    );

    expect($driver->generatePdf('h', null, null, new PdfOptions))->toBe('b-ok');
});

it('reports each captured exception via the ExceptionHandler', function () {
    $a = fakePdfDriver(new RuntimeException('a'));
    $b = fakePdfDriver(new RuntimeException('b'));
    $c = fakePdfDriver('ok');

    $handler = nullExceptionHandler();

    makeFallback(['a' => $a, 'b' => $b, 'c' => $c], $handler)
        ->generatePdf('h', null, null, new PdfOptions);

    expect($handler->reported)->toHaveCount(2);
    expect($handler->reported[0]->getMessage())->toBe('a');
    expect($handler->reported[1]->getMessage())->toBe('b');
});

it('savePdf falls back across drivers and writes to disk', function () {
    $a = fakePdfDriver(new RuntimeException('a'));
    $b = fakePdfDriver('saved-content');

    $driver = makeFallback(['a' => $a, 'b' => $b], nullExceptionHandler());

    $path = getTempPath('fallback-save-test.pdf');
    $driver->savePdf('h', null, null, new PdfOptions, $path);

    expect(file_get_contents($path))->toBe('saved-content');
    expect($a->saveCalls)->toBe(1);
    expect($b->saveCalls)->toBe(1);
});

it('skips a driver marked unhealthy in the cache', function () {
    $a = fakePdfDriver(new RuntimeException('a'));
    $b = fakePdfDriver('b-ok');

    $cache = Cache::store('array');
    $cache->put('laravel_pdf_driver_health_a', true, 600);

    $driver = makeFallback(
        ['a' => $a, 'b' => $b],
        nullExceptionHandler(),
        cache: $cache,
        ttl: 600,
    );

    expect($driver->generatePdf('h', null, null, new PdfOptions))->toBe('b-ok');
    expect($a->generateCalls)->toBe(0);
});

it('marks a driver unhealthy after a failure', function () {
    $a = fakePdfDriver(new RuntimeException('a'));
    $b = fakePdfDriver('b-ok');

    $cache = Cache::store('array');
    $cache->flush();

    makeFallback(
        ['a' => $a, 'b' => $b],
        nullExceptionHandler(),
        cache: $cache,
        ttl: 600,
    )->generatePdf('h', null, null, new PdfOptions);

    expect($cache->has('laravel_pdf_driver_health_a'))->toBeTrue();
    expect($cache->has('laravel_pdf_driver_health_b'))->toBeFalse();
});

it('uses the configured cache key prefix', function () {
    $a = fakePdfDriver(new RuntimeException('a'));
    $b = fakePdfDriver('b-ok');

    $cache = Cache::store('array');
    $cache->flush();

    makeFallback(
        ['a' => $a, 'b' => $b],
        nullExceptionHandler(),
        cache: $cache,
        ttl: 600,
        prefix: 'tenant1_pdf_',
    )->generatePdf('h', null, null, new PdfOptions);

    expect($cache->has('tenant1_pdf_a'))->toBeTrue();
});

it('does not write to cache when ttl is zero', function () {
    $a = fakePdfDriver(new RuntimeException('a'));
    $b = fakePdfDriver('b-ok');

    $cache = Cache::store('array');
    $cache->flush();

    makeFallback(
        ['a' => $a, 'b' => $b],
        nullExceptionHandler(),
        cache: $cache,
        ttl: 0,
    )->generatePdf('h', null, null, new PdfOptions);

    expect($cache->has('laravel_pdf_driver_health_a'))->toBeFalse();
});

it('does not interact with cache when repository is null', function () {
    $a = fakePdfDriver(new RuntimeException('a'));
    $b = fakePdfDriver('b-ok');

    $driver = makeFallback(
        ['a' => $a, 'b' => $b],
        nullExceptionHandler(),
        cache: null,
        ttl: 600,
    );

    expect($driver->generatePdf('h', null, null, new PdfOptions))->toBe('b-ok');
});

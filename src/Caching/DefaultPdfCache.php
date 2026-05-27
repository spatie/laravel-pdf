<?php

namespace Spatie\LaravelPdf\Caching;

use Closure;
use Illuminate\Support\Facades\Cache;

class DefaultPdfCache implements PdfCache
{
    public function remember(string $fingerprint, ?string $key, ?int $ttl, Closure $generate): string
    {
        $store = Cache::store(config('laravel-pdf.cache.store'));

        $cacheKey = $this->cacheKey($fingerprint, $key);

        $ttl ??= config('laravel-pdf.cache.ttl');

        $encode = fn () => base64_encode($generate());

        $cached = $ttl === null
            ? $store->rememberForever($cacheKey, $encode)
            : $store->remember($cacheKey, $ttl, $encode);

        return base64_decode($cached);
    }

    protected function cacheKey(string $fingerprint, ?string $key): string
    {
        $prefix = config('laravel-pdf.cache.prefix', 'laravel-pdf');

        return $prefix.':'.($key ?? sha1($fingerprint));
    }
}

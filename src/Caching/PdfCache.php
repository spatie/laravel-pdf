<?php

namespace Spatie\LaravelPdf\Caching;

use Closure;
use DateInterval;
use DateTimeInterface;

interface PdfCache
{
    /**
     * Return the cached PDF content for the given render, generating and
     * storing it first when it is not cached yet.
     *
     * @param  string  $fingerprint  A string uniquely describing the render inputs.
     * @param  ?string  $key  A user-supplied cache key that overrides the fingerprint.
     * @param  DateTimeInterface|DateInterval|int|null  $ttl  The lifetime in seconds (or as a date/interval), or null to cache forever.
     * @param  Closure(): string  $generate  Generates the (post-processed) PDF content.
     */
    public function remember(string $fingerprint, ?string $key, DateTimeInterface|DateInterval|int|null $ttl, Closure $generate): string;
}

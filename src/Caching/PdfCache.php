<?php

namespace Spatie\LaravelPdf\Caching;

use Closure;

interface PdfCache
{
    /**
     * Return the cached PDF content for the given render, generating and
     * storing it first when it is not cached yet.
     *
     * @param  string  $fingerprint  A string uniquely describing the render inputs.
     * @param  ?string  $key  A user-supplied cache key that overrides the fingerprint.
     * @param  ?int  $ttl  The lifetime in seconds, or null to cache forever.
     * @param  Closure(): string  $generate  Generates the (post-processed) PDF content.
     */
    public function remember(string $fingerprint, ?string $key, ?int $ttl, Closure $generate): string;
}

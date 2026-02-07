<?php

namespace Spatie\LaravelPdf;

use Closure;

class FakeQueuedPdfResponse extends QueuedPdfResponse
{
    public function __construct()
    {
        // No-op: skip parent constructor since we don't need a real PendingDispatch
    }

    public function then(Closure $callback): static
    {
        return $this;
    }

    public function catch(Closure $callback): static
    {
        return $this;
    }

    public function __call(string $method, array $parameters): static
    {
        return $this;
    }
}

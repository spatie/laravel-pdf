<?php

namespace Spatie\LaravelPdf;

/**
 * @mixin \Spatie\LaravelPdf\FakePdfBuilder
 */
class TestablePdf
{
    public function __construct(
        protected FakePdfBuilder $fake,
    ) {
        $this->fake->save('');
    }

    public function __call(string $method, array $args): static
    {
        $this->fake->{$method}(...$args);

        return $this;
    }
}

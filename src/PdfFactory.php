<?php

namespace Spatie\LaravelPdf;

class PdfFactory
{
    public function __call($method, $parameters): PdfBuilder
    {
        return (new PdfBuilder())->{$method}(...$parameters);
    }
}

<?php

namespace Spatie\LaravelPdf;

class PdfFactory
{
    protected static ?PdfBuilder $defaultPdfBuilder = null;

    public function __call($method, $parameters): PdfBuilder
    {
        $builder = new PdfBuilder();

        if (static::$defaultPdfBuilder) {
            $builder = clone static::$defaultPdfBuilder;
        }

        return $builder->{$method}(...$parameters);
    }

    public function default(): PdfBuilder
    {
        $pdfBuilder = new PdfBuilder();

        self::$defaultPdfBuilder = $pdfBuilder;

        return $pdfBuilder;
    }
}

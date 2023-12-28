<?php

namespace Spatie\LaravelPdf\Facades;

use Illuminate\Support\Facades\Facade;
use Spatie\LaravelPdf\FakePdfBuilder;
use Spatie\LaravelPdf\PdfBuilder;

/**
 * @mixin \Spatie\LaravelPdf\PdfBuilder
 * @mixin \Spatie\LaravelPdf\FakePdfBuilder
 */
class Pdf extends Facade
{
    protected static function getFacadeAccessor()
    {
        return PdfBuilder::class;
    }

    public static function fake()
    {
        $fake = new FakePdfBuilder();

        static::swap($fake);
    }

    public static function default(): PdfBuilder
    {
        $pdf = new PdfBuilder();

        static::swap($pdf);

        return $pdf;
    }
}

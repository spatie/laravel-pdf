<?php

namespace Spatie\LaravelPdf\Facades;

use Illuminate\Support\Facades\Facade;
use Spatie\LaravelPdf\FakePdf;

/**
 * @mixin \Spatie\LaravelPdf\PdfBuilder
 */
class Pdf extends Facade
{
    protected static function getFacadeAccessor()
    {
        return \Spatie\LaravelPdf\PdfBuilder::class;
    }

    public static function fake()
    {
        $fake = new FakePdf();

        static::swap($fake);
    }

    public static function default(): \Spatie\LaravelPdf\PdfBuilder
    {
        $pdf = new \Spatie\LaravelPdf\PdfBuilder();

        static::swap($pdf);

        return $pdf;
    }
}

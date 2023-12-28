<?php

namespace Spatie\LaravelPdf\Facades;

use Illuminate\Support\Facades\Facade;
use Spatie\LaravelPdf\FakePdfBuilder;
use Spatie\LaravelPdf\PdfFactory;

/**
 * @mixin \Spatie\LaravelPdf\PdfBuilder
 * @mixin \Spatie\LaravelPdf\FakePdfBuilder
 */
class Pdf extends Facade
{
    protected static function getFacadeAccessor()
    {
        return PdfFactory::class;
    }

    public static function fake()
    {
        $fake = new FakePdfBuilder();

        static::swap($fake);
    }
}

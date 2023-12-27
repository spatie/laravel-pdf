<?php

namespace Spatie\LaravelPdf\Facades;

use Illuminate\Support\Facades\Facade;
use Spatie\LaravelPdf\FakePdf;

/**
 * @mixin \Spatie\LaravelPdf\Pdf
 */
class Pdf extends Facade
{
    protected static function getFacadeAccessor()
    {
        return \Spatie\LaravelPdf\Pdf::class;
    }

    public static function fake()
    {
        $fake = new FakePdf();

        static::swap($fake);
    }

    public static function default(): \Spatie\LaravelPdf\Pdf
    {
        $pdf = new \Spatie\LaravelPdf\Pdf();

        static::swap($pdf);

        return $pdf;
    }
}

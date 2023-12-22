<?php

namespace Spatie\LaravelPdf\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Spatie\LaravelPdf\LaravelPdf
 */
class LaravelPdf extends Facade
{
    protected static function getFacadeAccessor()
    {
        return \Spatie\LaravelPdf\LaravelPdf::class;
    }
}

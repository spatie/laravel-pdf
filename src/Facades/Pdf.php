<?php

namespace Spatie\LaravelPdf\Facades;

use Illuminate\Support\Facades\Facade;
use Spatie\LaravelPdf\FakePdfBuilder;
use Spatie\LaravelPdf\PdfBuilder;
use Spatie\LaravelPdf\PdfFactory;

/**
 * @method static string decrypt(string $pathOrContents, string $password)
 *
 * @mixin PdfBuilder
 * @mixin FakePdfBuilder
 */
class Pdf extends Facade
{
    protected static function getFacadeAccessor()
    {
        return PdfFactory::class;
    }

    public static function fake(): FakePdfBuilder
    {
        $fake = new FakePdfBuilder;

        if ($callback = PdfFactory::defaultBuilder()->getCustomizeBrowsershotCallback()) {
            $fake->withBrowsershot($callback);
        }

        static::swap($fake);

        return $fake;
    }
}

<?php

namespace Spatie\LaravelPdf;

use Spatie\LaravelPdf\Encryption\PdfEncrypter;

class PdfFactory
{
    protected static ?PdfBuilder $defaultPdfBuilder = null;

    public function __construct()
    {
        self::defaultBuilder();
    }

    public function __call($method, $parameters): PdfBuilder
    {
        $builder = clone static::$defaultPdfBuilder;

        return $builder->{$method}(...$parameters);
    }

    public function decrypt(string $pdf, string $password): string
    {
        return app(PdfEncrypter::class)->decrypt($pdf, $password);
    }

    public function default(): PdfBuilder
    {
        $pdfBuilder = new PdfBuilder;

        self::$defaultPdfBuilder = $pdfBuilder;

        return $pdfBuilder;
    }

    public static function defaultBuilder(): PdfBuilder
    {
        if (self::$defaultPdfBuilder === null) {
            self::$defaultPdfBuilder = new PdfBuilder;
        }

        return self::$defaultPdfBuilder;
    }

    public static function resetDefaultBuilder(): void
    {
        self::$defaultPdfBuilder = null;
    }
}

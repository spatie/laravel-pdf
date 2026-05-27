<?php

namespace Spatie\LaravelPdf;

use SensitiveParameter;
use Spatie\LaravelPdf\Encryption\PdfEncrypter;
use Spatie\LaravelPdf\Exceptions\CouldNotDecryptPdf;

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

    public function decrypt(string $pathOrContents, #[SensitiveParameter] string $password): string
    {
        return app(PdfEncrypter::class)->decrypt($this->resolvePdfContents($pathOrContents), $password);
    }

    protected function resolvePdfContents(string $pathOrContents): string
    {
        if ($this->looksLikeContents($pathOrContents)) {
            return $pathOrContents;
        }

        if (! is_file($pathOrContents)) {
            throw CouldNotDecryptPdf::fileNotFound($pathOrContents);
        }

        $contents = file_get_contents($pathOrContents);

        if ($contents === false) {
            throw CouldNotDecryptPdf::fileNotFound($pathOrContents);
        }

        return $contents;
    }

    protected function looksLikeContents(string $pathOrContents): bool
    {
        return str_starts_with($pathOrContents, '%PDF')
            || str_contains($pathOrContents, "\0")
            || strlen($pathOrContents) > PHP_MAXPATHLEN;
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

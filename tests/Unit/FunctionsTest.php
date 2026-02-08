<?php

use Spatie\LaravelPdf\Facades\Pdf;
use Spatie\LaravelPdf\FakePdfBuilder;
use Spatie\LaravelPdf\PdfBuilder;

use function Spatie\LaravelPdf\Support\pdf;

test('the `pdf` function returns the pdf builder instance', function () {
    expect(pdf())->toBeInstanceOf(PdfBuilder::class);
});

test('the `pdf` function respects fakes', function () {
    Pdf::fake();

    expect(pdf())->toBeInstanceOf(FakePdfBuilder::class);
});

test('the `pdf` function accepts a view and parameters', function () {
    Pdf::fake();

    expect(pdf('foo', ['bar' => 'bax']))
        ->toBeInstanceOf(FakePdfBuilder::class)
        ->viewName->toBe('foo')
        ->viewData->toBe(['bar' => 'bax']);
});

test('the `pdf` function name accepts a name parameter and sets downloadName', function () {
    Pdf::fake();

    expect(pdf('foo', ['bar' => 'bax'])->name('baz.pdf'))
        ->toBeInstanceOf(FakePdfBuilder::class)
        ->viewName->toBe('foo')
        ->viewData->toBe(['bar' => 'bax'])
        ->downloadName->toBe('baz.pdf');
});

test('the `pdf` function download accepts a name parameter and sets downloadName', function () {
    Pdf::fake();

    expect(pdf('foo', ['bar' => 'bax'])->download('baz.pdf'))
        ->toBeInstanceOf(FakePdfBuilder::class)
        ->viewName->toBe('foo')
        ->viewData->toBe(['bar' => 'bax'])
        ->downloadName->toBe('baz.pdf');
});

test('the `pdf` function name accepts a name parameter and sets downloadName while calling download', function () {
    Pdf::fake();

    expect(pdf('foo', ['bar' => 'bax'])->name('baz.pdf')->download())
        ->toBeInstanceOf(FakePdfBuilder::class)
        ->viewName->toBe('foo')
        ->viewData->toBe(['bar' => 'bax'])
        ->downloadName->toBe('baz.pdf');
});

test('the `pdf` function download assigns the default to downloadName when no name is specified', function () {
    Pdf::fake();

    expect(pdf('foo', ['bar' => 'bax'])->download())
        ->toBeInstanceOf(FakePdfBuilder::class)
        ->viewName->toBe('foo')
        ->viewData->toBe(['bar' => 'bax'])
        ->downloadName->toBe('download.pdf');
});

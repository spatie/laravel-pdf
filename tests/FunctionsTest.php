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

<?php

use Illuminate\Support\Facades\Route;
use Spatie\LaravelPdf\Facades\Pdf;
use Spatie\LaravelPdf\PdfBuilder;

use function Spatie\LaravelPdf\Support\pdf;

beforeEach(function () {
    Pdf::fake();
});

it('can determine the view that was used', function () {
    Pdf::view('test')->save('my-custom-name.pdf');

    Pdf::assertViewIs('test');
});

it('can determine the view that was not used', function () {
    Pdf::view('test')->save('my-custom-name.pdf');

    Pdf::assertViewIs('this-view-does-not-exist');
})->fails();

it('can determine that a certain piece of data was passed to the view', function () {
    Pdf::view('test', ['foo' => 'bar'])->save('my-custom-name.pdf');

    Pdf::assertViewHas('foo');
});

it('can determine that a certain piece of data was not passed to the view', function () {
    Pdf::view('test', ['foo' => 'bar'])->save('my-custom-name.pdf');

    Pdf::assertViewHas('key-does-not-exist');
})->fails();

it('can determine that a certain piece of data was passed to the view with a certain value', function () {
    Pdf::view('test', ['foo' => 'bar'])->save('my-custom-name.pdf');

    Pdf::assertViewHas('foo', 'bar');
});

it('can determine that a certain piece of data was not passed to the view with a certain value', function () {
    Pdf::view('test', ['foo' => 'bar'])->save('my-custom-name.pdf');

    Pdf::assertViewHas('foo', 'this-value-does-not-exist');
})->fails();

it('can determine that the pdf content contains a certain string', function () {
    Pdf::view('test')->save('my-custom-name.pdf');

    Pdf::assertSee('test');
});

it('can determine that the pdf content does not contain a certain string', function () {
    Pdf::view('test')->save('my-custom-name.pdf');

    Pdf::assertSee('this-string-does-not-exist');
})->fails();

it('can determine properties of the pdf that was returned in a response', function () {
    Route::get('pdf', function () {
        return pdf('test')->inline();
    });

    $this->get('pdf')->assertSuccessful();

    Pdf::assertRespondedWithPdf(function (PdfBuilder $pdf) {
        return $pdf->viewName === 'test'
            && $pdf->isInline();
    });
});

it('can determine that a pdf did not have certain properties in a response', function () {
    Route::get('pdf', function () {
        return pdf('test')->inline();
    });

    $this->get('pdf')->assertSuccessful();

    Pdf::assertRespondedWithPdf(function (PdfBuilder $pdf) {
        return $pdf->isDownload();
    });
})->fails();

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

it('can determine the data that was passed to the view', function () {
    Route::get('pdf', function () {
        return pdf('test')->inline();
    });

    $this
        ->get('pdf')
        ->assertSuccessful();

    Pdf::assertRespondedWithPdf(function (PdfBuilder $pdf) {
        return $pdf->viewName === 'test'
            && $pdf->isInline();
    });
});

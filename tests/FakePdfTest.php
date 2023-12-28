<?php

use Illuminate\Support\Facades\Route;
use Spatie\LaravelPdf\Facades\Pdf;
use function \Spatie\LaravelPdf\Support\pdf;

it('can determine the view that was used', function () {
    Pdf::fake();

    Pdf::view('test');

    Pdf::assertViewIs('test');
});

it('can determine the data that was passed to the view', function () {
    Pdf::fake();

    Route::get('pdf', function() {
        return pdf('test')->inline();
    });

    $this
        ->get('pdf')
        ->assertSuccessful();

    Pdf::assertRespondedWithPdf(function(\Spatie\LaravelPdf\Pdf $pdf) {
        return $pdf->viewName === 'test'
            && $pdf->isInline();
    });
});

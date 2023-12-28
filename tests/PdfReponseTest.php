<?php

use Illuminate\Support\Facades\Route;

use function Spatie\LaravelPdf\Support\pdf;

it('can inline the pdf', function () {
    Route::get('inline-pdf', function () {
        return pdf('test')->inline('my-custom-name.pdf');
    });

    $this
        ->get('inline-pdf')
        ->assertHeader('content-type', 'application/pdf')
        ->assertHeader('content-disposition', 'inline; filename="my-custom-name.pdf"');
});

it('can download the pdf', function () {
    Route::get('download-pdf', function () {
        return pdf('test')->download('my-custom-name.pdf');
    });

    $this
        ->get('download-pdf')
        ->assertHeader('content-type', 'application/pdf')
        ->assertHeader('content-disposition', 'attachment; filename="my-custom-name.pdf"');
});

it('will tack on pdf to the filename if it is missing', function (string $method) {
    Route::get('pdf', function () use ($method) {
        return pdf('test')->{$method}('my-custom-name');
    });

    $headerMethod = $method === 'inline' ? 'inline' : 'attachment';

    $this
        ->get('pdf')
        ->assertHeader('content-disposition', $headerMethod . '; filename="my-custom-name.pdf"');
})->with(['inline', 'download']);

it('will inline the pdf by default', function () {
    Route::get('pdf', function () {
        return pdf('test')->name('my-custom-name.pdf');
    });

    $this
        ->get('pdf')
        ->assertHeader('Content-Type', 'application/pdf')
        ->assertHeader('Content-Disposition', 'inline; filename="my-custom-name.pdf"');
});

<?php

use Illuminate\Support\Facades\Route;

use function Spatie\LaravelPdf\Support\pdf;

Route::get('pdf/{method}', function ($method) {
    return pdf('test')->{$method}('my-custom-name');
});

Route::get('pdf', function () {
    return pdf('test')->name('my-custom-name.pdf');
});

Route::get('inline-pdf', function () {
    return pdf('test')->inline('my-custom-name.pdf');
});

Route::get('download-pdf', function () {
    return pdf('test')->download('my-custom-name.pdf');
});

Route::get('download-nameless-pdf', function () {
    return pdf('test')->download();
});

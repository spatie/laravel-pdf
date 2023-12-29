<?php

use Illuminate\Support\Facades\Route;

use function Spatie\LaravelPdf\Support\pdf;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::get("pdf/{method}", function ($method) {
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

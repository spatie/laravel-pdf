<?php

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Spatie\LaravelPdf\Drivers\GotenbergDriver;
use Spatie\LaravelPdf\Drivers\PdfDriver;
use Spatie\LaravelPdf\Enums\Orientation;
use Spatie\LaravelPdf\Exceptions\CouldNotGeneratePdf;
use Spatie\LaravelPdf\Facades\Pdf;
use Spatie\LaravelPdf\PdfOptions;

it('generates a pdf via gotenberg', function () {
    Http::fake([
        'localhost:3000/*' => Http::response('fake-pdf-content', 200),
    ]);

    $driver = new GotenbergDriver(['url' => 'http://localhost:3000']);

    $result = $driver->generatePdf('<h1>Hello</h1>', null, null, new PdfOptions);

    expect($result)->toBe('fake-pdf-content');

    Http::assertSent(function ($request) {
        return $request->url() === 'http://localhost:3000/forms/chromium/convert/html'
            && $request->isMultipart();
    });
});

it('sends html as index.html file', function () {
    Http::fake([
        'localhost:3000/*' => Http::response('fake-pdf', 200),
    ]);

    $driver = new GotenbergDriver(['url' => 'http://localhost:3000']);

    $driver->generatePdf('<h1>Hello</h1>', null, null, new PdfOptions);

    Http::assertSent(function ($request) {
        return collect($request->data())->contains(fn ($part) => ($part['filename'] ?? null) === 'index.html'
            && $part['contents'] === '<h1>Hello</h1>');
    });
});

it('sends printBackground by default', function () {
    Http::fake([
        'localhost:3000/*' => Http::response('fake-pdf', 200),
    ]);

    $driver = new GotenbergDriver(['url' => 'http://localhost:3000']);

    $driver->generatePdf('<h1>Hello</h1>', null, null, new PdfOptions);

    Http::assertSent(function ($request) {
        return collect($request->data())->contains(
            fn ($part) => ($part['name'] ?? null) === 'printBackground' && $part['contents'] === 'true'
        );
    });
});

it('sends format as paper dimensions', function () {
    Http::fake([
        'localhost:3000/*' => Http::response('fake-pdf', 200),
    ]);

    $driver = new GotenbergDriver(['url' => 'http://localhost:3000']);

    $options = new PdfOptions;
    $options->format = 'A4';

    $driver->generatePdf('<h1>Hello</h1>', null, null, $options);

    Http::assertSent(function ($request) {
        $data = collect($request->data());

        return $data->contains(fn ($part) => ($part['name'] ?? null) === 'paperWidth' && $part['contents'] === '8.27in')
            && $data->contains(fn ($part) => ($part['name'] ?? null) === 'paperHeight' && $part['contents'] === '11.69in');
    });
});

it('sends margins to gotenberg', function () {
    Http::fake([
        'localhost:3000/*' => Http::response('fake-pdf', 200),
    ]);

    $driver = new GotenbergDriver(['url' => 'http://localhost:3000']);

    $options = new PdfOptions;
    $options->margins = [
        'top' => 15,
        'right' => 10,
        'bottom' => 15,
        'left' => 10,
        'unit' => 'mm',
    ];

    $driver->generatePdf('<h1>Hello</h1>', null, null, $options);

    Http::assertSent(function ($request) {
        $data = collect($request->data());

        return $data->contains(fn ($part) => ($part['name'] ?? null) === 'marginTop' && $part['contents'] === '15mm')
            && $data->contains(fn ($part) => ($part['name'] ?? null) === 'marginRight' && $part['contents'] === '10mm')
            && $data->contains(fn ($part) => ($part['name'] ?? null) === 'marginBottom' && $part['contents'] === '15mm')
            && $data->contains(fn ($part) => ($part['name'] ?? null) === 'marginLeft' && $part['contents'] === '10mm');
    });
});

it('sends landscape option to gotenberg', function () {
    Http::fake([
        'localhost:3000/*' => Http::response('fake-pdf', 200),
    ]);

    $driver = new GotenbergDriver(['url' => 'http://localhost:3000']);

    $options = new PdfOptions;
    $options->orientation = Orientation::Landscape->value;

    $driver->generatePdf('<h1>Hello</h1>', null, null, $options);

    Http::assertSent(function ($request) {
        return collect($request->data())->contains(
            fn ($part) => ($part['name'] ?? null) === 'landscape' && $part['contents'] === 'true'
        );
    });
});

it('sends paper size to gotenberg', function () {
    Http::fake([
        'localhost:3000/*' => Http::response('fake-pdf', 200),
    ]);

    $driver = new GotenbergDriver(['url' => 'http://localhost:3000']);

    $options = new PdfOptions;
    $options->paperSize = [
        'width' => 200,
        'height' => 400,
        'unit' => 'mm',
    ];

    $driver->generatePdf('<h1>Hello</h1>', null, null, $options);

    Http::assertSent(function ($request) {
        $data = collect($request->data());

        return $data->contains(fn ($part) => ($part['name'] ?? null) === 'paperWidth' && $part['contents'] === '200mm')
            && $data->contains(fn ($part) => ($part['name'] ?? null) === 'paperHeight' && $part['contents'] === '400mm');
    });
});

it('sends scale option to gotenberg', function () {
    Http::fake([
        'localhost:3000/*' => Http::response('fake-pdf', 200),
    ]);

    $driver = new GotenbergDriver(['url' => 'http://localhost:3000']);

    $options = new PdfOptions;
    $options->scale = 0.5;

    $driver->generatePdf('<h1>Hello</h1>', null, null, $options);

    Http::assertSent(function ($request) {
        return collect($request->data())->contains(
            fn ($part) => ($part['name'] ?? null) === 'scale' && $part['contents'] === '0.5'
        );
    });
});

it('sends page ranges option to gotenberg', function () {
    Http::fake([
        'localhost:3000/*' => Http::response('fake-pdf', 200),
    ]);

    $driver = new GotenbergDriver(['url' => 'http://localhost:3000']);

    $options = new PdfOptions;
    $options->pageRanges = '1-3, 5';

    $driver->generatePdf('<h1>Hello</h1>', null, null, $options);

    Http::assertSent(function ($request) {
        return collect($request->data())->contains(
            fn ($part) => ($part['name'] ?? null) === 'nativePageRanges' && $part['contents'] === '1-3, 5'
        );
    });
});

it('sends tagged option to gotenberg', function () {
    Http::fake([
        'localhost:3000/*' => Http::response('fake-pdf', 200),
    ]);

    $driver = new GotenbergDriver(['url' => 'http://localhost:3000']);

    $options = new PdfOptions;
    $options->tagged = true;

    $driver->generatePdf('<h1>Hello</h1>', null, null, $options);

    Http::assertSent(function ($request) {
        return collect($request->data())->contains(
            fn ($part) => ($part['name'] ?? null) === 'generateTaggedPdf' && $part['contents'] === 'true'
        );
    });
});

it('does not send optional fields when not set', function () {
    Http::fake([
        'localhost:3000/*' => Http::response('fake-pdf', 200),
    ]);

    $driver = new GotenbergDriver(['url' => 'http://localhost:3000']);

    $driver->generatePdf('<h1>Hello</h1>', null, null, new PdfOptions);

    Http::assertSent(function ($request) {
        $data = collect($request->data());
        $names = $data->pluck('name')->filter();

        return ! $names->contains('scale')
            && ! $names->contains('nativePageRanges')
            && ! $names->contains('generateTaggedPdf')
            && ! $names->contains('landscape')
            && ! $names->contains('paperWidth');
    });
});

it('sends header and footer as html files', function () {
    Http::fake([
        'localhost:3000/*' => Http::response('fake-pdf', 200),
    ]);

    $driver = new GotenbergDriver(['url' => 'http://localhost:3000']);

    $driver->generatePdf('<h1>Body</h1>', '<div>Header</div>', '<div>Footer</div>', new PdfOptions);

    Http::assertSent(function ($request) {
        $data = collect($request->data());

        return $data->contains(fn ($part) => ($part['filename'] ?? null) === 'header.html' && $part['contents'] === '<div>Header</div>')
            && $data->contains(fn ($part) => ($part['filename'] ?? null) === 'footer.html' && $part['contents'] === '<div>Footer</div>');
    });
});

it('saves a pdf to disk via gotenberg', function () {
    Http::fake([
        'localhost:3000/*' => Http::response('fake-pdf-content', 200),
    ]);

    $driver = new GotenbergDriver(['url' => 'http://localhost:3000']);

    $path = getTempPath('gotenberg-test.pdf');
    $driver->savePdf('<h1>Hello</h1>', null, null, new PdfOptions, $path);

    expect(file_get_contents($path))->toBe('fake-pdf-content');
});

it('throws on gotenberg api failure', function () {
    Http::fake([
        'localhost:3000/*' => Http::response('error message', 500),
    ]);

    $driver = new GotenbergDriver(['url' => 'http://localhost:3000']);

    $driver->generatePdf('<h1>Hello</h1>', null, null, new PdfOptions);
})->throws(CouldNotGeneratePdf::class, 'Gotenberg PDF generation failed');

it('defaults to localhost:3000 when no url configured', function () {
    Http::fake([
        'localhost:3000/*' => Http::response('fake-pdf', 200),
    ]);

    $driver = new GotenbergDriver;

    $driver->generatePdf('<h1>Hello</h1>', null, null, new PdfOptions);

    Http::assertSent(function ($request) {
        return $request->url() === 'http://localhost:3000/forms/chromium/convert/html';
    });
});

it('can switch to gotenberg driver at runtime', function () {
    Http::fake([
        'localhost:3000/*' => Http::response('fake-pdf-content', 200),
    ]);

    Config::set('laravel-pdf.gotenberg', [
        'url' => 'http://localhost:3000',
    ]);

    app()->forgetInstance('laravel-pdf.driver.gotenberg');

    $path = getTempPath('runtime-switch-gotenberg.pdf');

    Pdf::html('<h1>Hello</h1>')
        ->driver('gotenberg')
        ->save($path);

    expect(file_get_contents($path))->toBe('fake-pdf-content');

    Http::assertSent(function ($request) {
        return str_contains($request->url(), '/forms/chromium/convert/html');
    });
});

it('uses gotenberg as default driver when configured', function () {
    Http::fake([
        'localhost:3000/*' => Http::response('fake-pdf-content', 200),
    ]);

    Config::set('laravel-pdf.driver', 'gotenberg');
    Config::set('laravel-pdf.gotenberg', [
        'url' => 'http://localhost:3000',
    ]);

    app()->forgetInstance(PdfDriver::class);
    app()->forgetInstance('laravel-pdf.driver.gotenberg');

    $path = getTempPath('default-gotenberg.pdf');

    Pdf::html('<h1>Hello</h1>')->save($path);

    expect(file_get_contents($path))->toBe('fake-pdf-content');
});

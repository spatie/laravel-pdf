<?php

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Spatie\LaravelPdf\Drivers\CloudflareDriver;
use Spatie\LaravelPdf\Drivers\PdfDriver;
use Spatie\LaravelPdf\Enums\Orientation;
use Spatie\LaravelPdf\Exceptions\CouldNotGeneratePdf;
use Spatie\LaravelPdf\Facades\Pdf;
use Spatie\LaravelPdf\PdfOptions;

it('throws when api token or account id is missing', function () {
    new CloudflareDriver([]);
})->throws(CouldNotGeneratePdf::class, 'The Cloudflare driver requires both an API token and account ID.');

it('generates a pdf via the cloudflare api', function () {
    Http::fake([
        'api.cloudflare.com/*' => Http::response('fake-pdf-content', 200),
    ]);

    $driver = new CloudflareDriver([
        'api_token' => 'test-token',
        'account_id' => 'test-account',
    ]);

    $result = $driver->generatePdf('<h1>Hello</h1>', null, null, new PdfOptions);

    expect($result)->toBe('fake-pdf-content');

    Http::assertSent(function ($request) {
        return $request->url() === 'https://api.cloudflare.com/client/v4/accounts/test-account/browser-rendering/pdf'
            && $request->hasHeader('Authorization', 'Bearer test-token')
            && $request['html'] === '<h1>Hello</h1>'
            && $request['pdfOptions']['printBackground'] === true;
    });
});

it('sends format option to cloudflare', function () {
    Http::fake([
        'api.cloudflare.com/*' => Http::response('fake-pdf', 200),
    ]);

    $driver = new CloudflareDriver([
        'api_token' => 'test-token',
        'account_id' => 'test-account',
    ]);

    $options = new PdfOptions;
    $options->format = 'A4';

    $driver->generatePdf('<h1>Hello</h1>', null, null, $options);

    Http::assertSent(function ($request) {
        return $request['pdfOptions']['format'] === 'a4';
    });
});

it('sends margins to cloudflare', function () {
    Http::fake([
        'api.cloudflare.com/*' => Http::response('fake-pdf', 200),
    ]);

    $driver = new CloudflareDriver([
        'api_token' => 'test-token',
        'account_id' => 'test-account',
    ]);

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
        return $request['pdfOptions']['margin']['top'] === '15mm'
            && $request['pdfOptions']['margin']['right'] === '10mm'
            && $request['pdfOptions']['margin']['bottom'] === '15mm'
            && $request['pdfOptions']['margin']['left'] === '10mm';
    });
});

it('sends landscape option to cloudflare', function () {
    Http::fake([
        'api.cloudflare.com/*' => Http::response('fake-pdf', 200),
    ]);

    $driver = new CloudflareDriver([
        'api_token' => 'test-token',
        'account_id' => 'test-account',
    ]);

    $options = new PdfOptions;
    $options->orientation = Orientation::Landscape->value;

    $driver->generatePdf('<h1>Hello</h1>', null, null, $options);

    Http::assertSent(function ($request) {
        return $request['pdfOptions']['landscape'] === true;
    });
});

it('sends paper size to cloudflare', function () {
    Http::fake([
        'api.cloudflare.com/*' => Http::response('fake-pdf', 200),
    ]);

    $driver = new CloudflareDriver([
        'api_token' => 'test-token',
        'account_id' => 'test-account',
    ]);

    $options = new PdfOptions;
    $options->paperSize = [
        'width' => 200,
        'height' => 400,
        'unit' => 'mm',
    ];

    $driver->generatePdf('<h1>Hello</h1>', null, null, $options);

    Http::assertSent(function ($request) {
        return $request['pdfOptions']['width'] === '200mm'
            && $request['pdfOptions']['height'] === '400mm';
    });
});

it('sends header and footer templates to cloudflare', function () {
    Http::fake([
        'api.cloudflare.com/*' => Http::response('fake-pdf', 200),
    ]);

    $driver = new CloudflareDriver([
        'api_token' => 'test-token',
        'account_id' => 'test-account',
    ]);

    $driver->generatePdf('<h1>Body</h1>', '<div>Header</div>', '<div>Footer</div>', new PdfOptions);

    Http::assertSent(function ($request) {
        return $request['pdfOptions']['headerTemplate'] === '<div>Header</div>'
            && $request['pdfOptions']['footerTemplate'] === '<div>Footer</div>'
            && $request['pdfOptions']['displayHeaderFooter'] === true;
    });
});

it('saves a pdf to disk via cloudflare', function () {
    Http::fake([
        'api.cloudflare.com/*' => Http::response('fake-pdf-content', 200),
    ]);

    $driver = new CloudflareDriver([
        'api_token' => 'test-token',
        'account_id' => 'test-account',
    ]);

    $path = getTempPath('cloudflare-test.pdf');
    $driver->savePdf('<h1>Hello</h1>', null, null, new PdfOptions, $path);

    expect(file_get_contents($path))->toBe('fake-pdf-content');
});

it('throws on cloudflare api failure', function () {
    Http::fake([
        'api.cloudflare.com/*' => Http::response('error message', 500),
    ]);

    $driver = new CloudflareDriver([
        'api_token' => 'test-token',
        'account_id' => 'test-account',
    ]);

    $driver->generatePdf('<h1>Hello</h1>', null, null, new PdfOptions);
})->throws(CouldNotGeneratePdf::class, 'Cloudflare PDF generation failed');

it('can switch to cloudflare driver at runtime', function () {
    Http::fake([
        'api.cloudflare.com/*' => Http::response('fake-pdf-content', 200),
    ]);

    Config::set('laravel-pdf.cloudflare', [
        'api_token' => 'test-token',
        'account_id' => 'test-account',
    ]);

    // Force re-registration of the cloudflare driver singleton
    app()->forgetInstance('laravel-pdf.driver.cloudflare');

    $path = getTempPath('runtime-switch.pdf');

    Pdf::html('<h1>Hello</h1>')
        ->driver('cloudflare')
        ->save($path);

    expect(file_get_contents($path))->toBe('fake-pdf-content');

    Http::assertSent(function ($request) {
        return str_contains($request->url(), 'browser-rendering/pdf');
    });
});

it('uses cloudflare as default driver when configured', function () {
    Http::fake([
        'api.cloudflare.com/*' => Http::response('fake-pdf-content', 200),
    ]);

    Config::set('laravel-pdf.driver', 'cloudflare');
    Config::set('laravel-pdf.cloudflare', [
        'api_token' => 'test-token',
        'account_id' => 'test-account',
    ]);

    // Force re-registration of singletons
    app()->forgetInstance(PdfDriver::class);
    app()->forgetInstance('laravel-pdf.driver.cloudflare');

    $path = getTempPath('default-cloudflare.pdf');

    Pdf::html('<h1>Hello</h1>')->save($path);

    expect(file_get_contents($path))->toBe('fake-pdf-content');
});

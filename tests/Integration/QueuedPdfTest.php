<?php

use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Config;
use Spatie\LaravelPdf\Drivers\PdfDriver;
use Spatie\LaravelPdf\Exceptions\CouldNotGeneratePdf;
use Spatie\LaravelPdf\Facades\Pdf;
use Spatie\LaravelPdf\Jobs\GeneratePdfJob;
use Spatie\LaravelPdf\QueuedPdfResponse;

class CustomGeneratePdfJob extends GeneratePdfJob
{
    public int $tries = 3;

    public int $timeout = 120;
}

beforeEach(function () {
    Config::set('laravel-pdf.driver', 'dompdf');
    Config::set('laravel-pdf.dompdf', []);

    app()->forgetInstance(PdfDriver::class);
    app()->forgetInstance('laravel-pdf.driver.dompdf');
});

it('dispatches a job when calling saveQueued', function () {
    Bus::fake();

    Pdf::html('<h1>Test</h1>')->saveQueued('test.pdf');

    Bus::assertDispatched(GeneratePdfJob::class);
});

it('returns a QueuedPdfResponse', function () {
    Bus::fake();

    $response = Pdf::html('<h1>Test</h1>')->saveQueued('test.pdf');

    expect($response)->toBeInstanceOf(QueuedPdfResponse::class);
});

it('throws when withBrowsershot is used with saveQueued', function () {
    Pdf::html('<h1>Test</h1>')
        ->withBrowsershot(function () {})
        ->saveQueued('test.pdf');
})->throws(CouldNotGeneratePdf::class, 'Cannot use saveQueued() with withBrowsershot()');

it('uses a custom job class from config', function () {
    Bus::fake();

    Config::set('laravel-pdf.job', CustomGeneratePdfJob::class);

    Pdf::html('<h1>Test</h1>')->saveQueued('test.pdf');

    Bus::assertDispatched(CustomGeneratePdfJob::class);
});

it('generates a pdf when the job runs synchronously', function () {
    $path = getTempPath('queued-sync.pdf');

    Pdf::html('<h1>Queued sync test</h1>')
        ->driver('dompdf')
        ->saveQueued($path);

    // Force the pending dispatch to run by going out of scope
    // The PendingDispatch dispatches on destruct, but with sync driver it runs immediately
    // Let's dispatch directly instead
    $job = new GeneratePdfJob(
        html: '<h1>Queued sync test</h1>',
        headerHtml: null,
        footerHtml: null,
        options: new \Spatie\LaravelPdf\PdfOptions,
        path: $path,
        driverName: 'dompdf',
    );

    dispatch_sync($job);

    expect(file_exists($path))->toBeTrue();
    expect(file_get_contents($path))->toStartWith('%PDF');
});

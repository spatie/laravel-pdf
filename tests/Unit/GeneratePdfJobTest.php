<?php

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Storage;
use Spatie\LaravelPdf\Drivers\PdfDriver;
use Spatie\LaravelPdf\Jobs\GeneratePdfJob;
use Spatie\LaravelPdf\PdfOptions;

beforeEach(function () {
    Config::set('laravel-pdf.driver', 'dompdf');
    Config::set('laravel-pdf.dompdf', []);

    app()->forgetInstance(PdfDriver::class);
    app()->forgetInstance('laravel-pdf.driver.dompdf');
});

it('saves a pdf to a local path', function () {
    $path = getTempPath('queued-local.pdf');

    $job = new GeneratePdfJob(
        html: '<h1>Hello from job</h1>',
        headerHtml: null,
        footerHtml: null,
        options: new PdfOptions,
        path: $path,
        driverName: 'dompdf',
    );

    $job->handle();

    expect(file_exists($path))->toBeTrue();
    expect(file_get_contents($path))->toStartWith('%PDF');
});

it('saves a pdf to a disk', function () {
    Storage::fake('testing');

    $job = new GeneratePdfJob(
        html: '<h1>Hello from disk job</h1>',
        headerHtml: null,
        footerHtml: null,
        options: new PdfOptions,
        path: 'invoices/test.pdf',
        diskName: 'testing',
        driverName: 'dompdf',
    );

    $job->handle();

    Storage::disk('testing')->assertExists('invoices/test.pdf');
});

it('resolves the correct driver by name', function () {
    $path = getTempPath('queued-driver.pdf');

    $job = new GeneratePdfJob(
        html: '<h1>Driver test</h1>',
        headerHtml: null,
        footerHtml: null,
        options: new PdfOptions,
        path: $path,
        driverName: 'dompdf',
    );

    $job->handle();

    expect(file_exists($path))->toBeTrue();
    expect(file_get_contents($path))->toStartWith('%PDF');
});

it('invokes then callback on success', function () {
    $path = getTempPath('queued-then.pdf');
    $callbackPath = null;

    $job = new GeneratePdfJob(
        html: '<h1>Then test</h1>',
        headerHtml: null,
        footerHtml: null,
        options: new PdfOptions,
        path: $path,
        driverName: 'dompdf',
    );

    $job->then(function (string $path) use (&$callbackPath) {
        $callbackPath = $path;
    });

    $job->handle();

    expect($callbackPath)->toBe($path);
});

it('passes disk name to then callback', function () {
    Storage::fake('testing');

    $callbackDisk = null;

    $job = new GeneratePdfJob(
        html: '<h1>Disk callback test</h1>',
        headerHtml: null,
        footerHtml: null,
        options: new PdfOptions,
        path: 'invoices/test.pdf',
        diskName: 'testing',
        driverName: 'dompdf',
    );

    $job->then(function (string $path, ?string $diskName) use (&$callbackDisk) {
        $callbackDisk = $diskName;
    });

    $job->handle();

    expect($callbackDisk)->toBe('testing');
});

it('passes null disk name to then callback for local saves', function () {
    $path = getTempPath('queued-local-disk.pdf');
    $callbackDisk = 'not-null';

    $job = new GeneratePdfJob(
        html: '<h1>Local callback test</h1>',
        headerHtml: null,
        footerHtml: null,
        options: new PdfOptions,
        path: $path,
        driverName: 'dompdf',
    );

    $job->then(function (string $path, ?string $diskName) use (&$callbackDisk) {
        $callbackDisk = $diskName;
    });

    $job->handle();

    expect($callbackDisk)->toBeNull();
});

it('saves a pdf with metadata to a local path', function () {
    $path = getTempPath('queued-metadata.pdf');

    $job = new GeneratePdfJob(
        html: '<h1>Hello with metadata</h1>',
        headerHtml: null,
        footerHtml: null,
        options: new PdfOptions,
        path: $path,
        driverName: 'dompdf',
        metadata: new \Spatie\LaravelPdf\PdfMetadata(title: 'Queued Invoice', author: 'Acme Corp'),
    );

    $job->handle();

    expect(file_exists($path))->toBeTrue();

    $content = file_get_contents($path);
    expect($content)->toStartWith('%PDF');
    expect($content)->toContain('/Title (Queued Invoice)');
    expect($content)->toContain('/Author (Acme Corp)');
});

it('saves a pdf with metadata to a disk', function () {
    Storage::fake('testing');

    $job = new GeneratePdfJob(
        html: '<h1>Disk metadata test</h1>',
        headerHtml: null,
        footerHtml: null,
        options: new PdfOptions,
        path: 'invoices/meta.pdf',
        diskName: 'testing',
        driverName: 'dompdf',
        metadata: new \Spatie\LaravelPdf\PdfMetadata(title: 'Disk Invoice'),
    );

    $job->handle();

    Storage::disk('testing')->assertExists('invoices/meta.pdf');

    $content = Storage::disk('testing')->get('invoices/meta.pdf');
    expect($content)->toContain('/Title (Disk Invoice)');
});

it('invokes catch callback on failure', function () {
    $caughtException = null;

    $job = new GeneratePdfJob(
        html: '<h1>Catch test</h1>',
        headerHtml: null,
        footerHtml: null,
        options: new PdfOptions,
        path: '/nonexistent/directory/test.pdf',
        driverName: 'dompdf',
    );

    $job->catch(function (\Throwable $e) use (&$caughtException) {
        $caughtException = $e;
    });

    $job->failed(new \RuntimeException('Test failure'));

    expect($caughtException)->toBeInstanceOf(\RuntimeException::class);
    expect($caughtException->getMessage())->toBe('Test failure');
});

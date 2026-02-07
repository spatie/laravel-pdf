<?php

use Spatie\LaravelPdf\Facades\Pdf;
use Spatie\LaravelPdf\FakeQueuedPdfResponse;
use Spatie\LaravelPdf\PdfBuilder;

beforeEach(function () {
    Pdf::fake();
});

it('can assert a pdf was queued with a string path', function () {
    Pdf::view('test')->saveQueued('queued.pdf');

    Pdf::assertQueued('queued.pdf');
});

it('can assert a pdf was queued with a callable', function () {
    Pdf::view('test')->saveQueued('queued.pdf');

    Pdf::assertQueued(function (PdfBuilder $pdf, string $path) {
        return $path === 'queued.pdf' && $pdf->viewName === 'test';
    });
});

it('fails when asserting a queued pdf that was not queued', function () {
    Pdf::assertQueued('nonexistent.pdf');
})->fails();

it('fails when asserting a queued pdf with callable that does not match', function () {
    Pdf::view('test')->saveQueued('queued.pdf');

    Pdf::assertQueued(function (PdfBuilder $pdf, string $path) {
        return $path === 'other.pdf';
    });
})->fails();

it('can assert no pdfs were queued', function () {
    Pdf::assertNotQueued();
});

it('can assert a specific path was not queued', function () {
    Pdf::view('test')->saveQueued('queued.pdf');

    Pdf::assertNotQueued('other.pdf');
});

it('fails assertNotQueued when a pdf was queued', function () {
    Pdf::view('test')->saveQueued('queued.pdf');

    Pdf::assertNotQueued();
})->fails();

it('fails assertNotQueued with path when that path was queued', function () {
    Pdf::view('test')->saveQueued('queued.pdf');

    Pdf::assertNotQueued('queued.pdf');
})->fails();

it('returns a chainable fake response from saveQueued', function () {
    $response = Pdf::view('test')->saveQueued('queued.pdf');

    expect($response)->toBeInstanceOf(FakeQueuedPdfResponse::class);

    $chained = $response
        ->then(fn () => null)
        ->catch(fn () => null)
        ->onQueue('pdfs');

    expect($chained)->toBeInstanceOf(FakeQueuedPdfResponse::class);
});

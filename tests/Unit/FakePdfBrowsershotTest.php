<?php

use Illuminate\Support\Facades\Route;
use Spatie\Browsershot\Browsershot;
use Spatie\LaravelPdf\Facades\Pdf;

use function Spatie\LaravelPdf\Support\pdf;

function browsershotUsesRemoteInstance(Browsershot $browsershot, string $url): bool
{
    return (invade($browsershot)->additionalOptions['remoteInstanceUrl'] ?? null) === $url;
}

it('can assert the browsershot configuration of a saved fake pdf', function () {
    Pdf::fake();

    Pdf::view('test')
        ->withBrowsershot(function (Browsershot $browsershot) {
            $browsershot->setRemoteInstance('127.0.0.1', 9222);
        })
        ->save('remote.pdf');

    Pdf::assertBrowsershot(
        fn (Browsershot $browsershot) => browsershotUsesRemoteInstance($browsershot, 'http://127.0.0.1:9222'),
    );
});

it('can assert the browsershot configuration of a fake pdf response', function () {
    Pdf::fake();

    Route::get('pdf', function () {
        return pdf('test')
            ->withBrowsershot(function (Browsershot $browsershot) {
                $browsershot->setRemoteInstance('127.0.0.1', 9222);
            })
            ->inline();
    });

    $this->get('pdf')->assertSuccessful();

    Pdf::assertBrowsershot(
        fn (Browsershot $browsershot) => browsershotUsesRemoteInstance($browsershot, 'http://127.0.0.1:9222'),
    );
});

it('fails when no fake pdf matches the browsershot expectations', function () {
    Pdf::fake();

    Pdf::view('test')->save('plain.pdf');

    Pdf::assertBrowsershot(
        fn (Browsershot $browsershot) => browsershotUsesRemoteInstance($browsershot, 'http://127.0.0.1:9222'),
    );
})->fails();

it('uses the browsershot configuration from the default builder when faking', function () {
    Pdf::default()->withBrowsershot(function (Browsershot $browsershot) {
        $browsershot->setRemoteInstance('127.0.0.1', 9222);
    });

    Pdf::fake();

    Pdf::view('test')->save('remote.pdf');

    Pdf::assertBrowsershot(
        fn (Browsershot $browsershot) => browsershotUsesRemoteInstance($browsershot, 'http://127.0.0.1:9222'),
    );
});

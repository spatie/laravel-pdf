<?php

use Spatie\LaravelPdf\Exceptions\CouldNotGeneratePdf;
use Spatie\LaravelPdf\Facades\Pdf;
use Spatie\LaravelPdf\Tests\TestSupport\FakeDriver;

it('does not wait for readiness by default', function () {
    $builder = Pdf::html('<p>hi</p>');

    $options = invade($builder)->buildOptions();

    expect($options->waitForReady)->toBeNull();
});

it('waits for the default readiness flag', function () {
    $builder = Pdf::html('<p>hi</p>')->waitUntilReady();

    $options = invade($builder)->buildOptions();

    expect($options->waitForReady)->toBe('window.pdfReady === true');
});

it('waits for a custom readiness expression with a timeout', function () {
    $builder = Pdf::html('<p>hi</p>')->waitUntilReady('window.ready', 5000);

    $options = invade($builder)->buildOptions();

    expect($options->waitForReady)->toBe('window.ready');
    expect($options->waitForReadyTimeout)->toBe(5000);
});

it('passes the readiness expression to a supporting driver', function () {
    $driver = new FakeDriver;

    Pdf::html('<p>hi</p>')->setDriver($driver)->waitUntilReady()->base64();

    expect($driver->lastOptions->waitForReady)->toBe('window.pdfReady === true');
});

it('throws when the active driver does not support readiness', function () {
    Pdf::html('<p>hi</p>')->driver('dompdf')->waitUntilReady()->base64();
})->throws(CouldNotGeneratePdf::class, 'does not support waiting for readiness');

<?php

use Spatie\LaravelPdf\Enums\Orientation;
use Spatie\LaravelPdf\Facades\Pdf;
use Spatie\LaravelPdf\PdfFactory;

beforeEach(function () {
    // Reset defaults between tests to ensure isolation
    PdfFactory::resetDefaultBuilder();
});

it('can set defaults for pdfs', function () {
    Pdf::default()->orientation(Orientation::Landscape);

    $firstPath = getTempPath('first.pdf');
    Pdf::html('test')->save($firstPath);

    expect($firstPath)
        ->toHaveDimensions(792, 612)
        ->toContainText('test');

    $secondPath = getTempPath('second.pdf');
    Pdf::html('test')->save($secondPath);

    expect($secondPath)
        ->toHaveDimensions(792, 612)
        ->toContainText('test');
});

it('defaults to the letter format', function () {
    $targetPath = getTempPath('test.pdf');
    Pdf::view('test')->save($targetPath);

    expect($targetPath)->toHaveDimensions(612, 792);
});

it('will not use properties of the previous pdf when not setting a default', function () {
    $firstPath = getTempPath('first.pdf');
    Pdf::html('test')
        ->orientation(Orientation::Landscape)
        ->save($firstPath);

    expect($firstPath)
        ->toHaveDimensions(792, 612)
        ->toContainText('test');

    $secondPath = getTempPath('second.pdf');
    Pdf::html('test')->save($secondPath);

    expect($secondPath)
        ->toHaveDimensions(792, 612)
        ->toContainText('test');
})->fails();

it('preserves defaults after the facade is cleared (simulating queue:work behavior)', function () {
    // Set defaults - this is typically done in AppServiceProvider::boot()
    Pdf::default()->orientation(Orientation::Landscape);

    // Verify defaults work before clearing
    $firstPath = getTempPath('first.pdf');
    Pdf::html('test')->save($firstPath);
    expect($firstPath)->toHaveDimensions(792, 612);

    // Simulate what happens in queue:work when the container is flushed
    // or the facade resolved instance is cleared between job executions
    Pdf::clearResolvedInstance(PdfFactory::class);

    // The next call to the facade will resolve a new PdfFactory instance
    // The defaults should still be preserved
    $secondPath = getTempPath('second.pdf');
    Pdf::html('test')->save($secondPath);

    expect($secondPath)
        ->toHaveDimensions(792, 612) // Should still be landscape
        ->toContainText('test');
});

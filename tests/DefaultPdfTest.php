<?php

use Spatie\LaravelPdf\Enums\Orientation;
use Spatie\LaravelPdf\Facades\Pdf;

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
    Pdf::view('test')->save($this->targetPath);

    expect($this->targetPath)->toHaveDimensions(612, 792);
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

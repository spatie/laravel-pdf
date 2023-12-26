<?php

use Spatie\LaravelPdf\Enums\Orientation;
use Spatie\LaravelPdf\Facades\Pdf;

beforeEach(function () {
    $this->targetPath = getTempPath('test.pdf');
});

it('can set defaults for pdfs', function() {
    Pdf::default()
        ->view('test')
        ->orientation(Orientation::Landscape);

    Pdf::save($this->targetPath);

    expect($this->targetPath)
        ->toHaveDimensions(792, 612)
        ->toContainText('This is a test');
});

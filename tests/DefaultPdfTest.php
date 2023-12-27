<?php

use Spatie\LaravelPdf\Enums\Orientation;
use Spatie\LaravelPdf\Facades\Pdf;

beforeEach(function () {
    $this->targetPath = getTempPath('test.pdf');
});

it('can set defaults for pdfs', function () {
    Pdf::default()
        ->headerHtml('header html')
        ->orientation(Orientation::Landscape);

    Pdf::save($this->targetPath);

    expect($this->targetPath)
        ->toHaveDimensions(792, 612)
        ->toContainText('header html');
});

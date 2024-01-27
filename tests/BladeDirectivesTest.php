<?php

use Spatie\LaravelPdf\Facades\Pdf;

beforeEach(function () {
    $this->targetPath = getTempPath('test.pdf');
});

it('can set a page break', function () {
    Pdf::view('blade-directives.body')
        ->save($this->targetPath);

    expect($this->targetPath)->toHavePageCount(2);
});

it('can display the number of pages', function () {
    Pdf::view('blade-directives.body')
        ->footerView('blade-directives.footer')
        ->save($this->targetPath);

    expect($this->targetPath)->toContainText('page 1 of 2');
});

it('can display the print color adjust css directive', function () {
    Pdf::view('blade-directives.body')
        ->save($this->targetPath);

    expect($this->targetPath)->toContainText('-webkit-print-color-adjust: exact;');

    Pdf::view('blade-directives.body-with-economy-colors')
        ->save($this->targetPath);

    expect($this->targetPath)->toContainText('-webkit-print-color-adjust: economy;');
});

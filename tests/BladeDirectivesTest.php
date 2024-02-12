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

it('can display an image', function () {
    Pdf::view('blade-directives.body')
        ->headerView('blade-directives.header')
        ->save($this->targetPath);

    expect($this->targetPath)->toContainText('<img src="data:image/png;base64,');
});

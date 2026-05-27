<?php

use Spatie\LaravelPdf\Facades\Pdf;

beforeEach(function () {
    $this->targetPath = getTempPath('readiness.pdf');
});

it('waits for the readiness flag before capturing the pdf', function () {
    retryOnFlake(function () {
        Pdf::view('readiness')
            ->waitUntilReady()
            ->save($this->targetPath);

        expect($this->targetPath)->toContainText('ready for capture');
    });
});

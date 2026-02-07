<?php

it('can inline the pdf', function () {
    $this
        ->get('inline-pdf')
        ->assertHeader('content-type', 'application/pdf')
        ->assertHeader('content-disposition', 'inline; filename="my-custom-name.pdf"');
});

it('can download the pdf with a name', function () {
    $this
        ->get('download-pdf')
        ->assertHeader('content-type', 'application/pdf')
        ->assertHeader('content-disposition', 'attachment; filename="my-custom-name.pdf"');
});

it('can download the pdf without a name', function () {
    $this
        ->get('download-nameless-pdf')
        ->assertHeader('content-type', 'application/pdf')
        ->assertHeader('content-disposition', 'attachment; filename="download.pdf"');
});

it('will tack on pdf to the filename if it is missing', function (string $method) {
    $headerMethod = $method === 'inline' ? 'inline' : 'attachment';

    $this
        ->get("pdf/{$method}")
        ->assertHeader('content-disposition', $headerMethod.'; filename="my-custom-name.pdf"');
})->with(['inline', 'download']);

it('will inline the pdf by default', function () {

    $this
        ->get('pdf')
        ->assertHeader('Content-Type', 'application/pdf')
        ->assertHeader('Content-Disposition', 'inline; filename="my-custom-name.pdf"');
});

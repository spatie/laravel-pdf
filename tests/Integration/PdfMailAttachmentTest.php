<?php

use Illuminate\Contracts\Mail\Attachable;
use Illuminate\Mail\Attachment;
use Spatie\LaravelPdf\Facades\Pdf;
use Spatie\LaravelPdf\PdfBuilder;

it('implements the Attachable contract', function () {
    expect(Pdf::view('test'))->toBeInstanceOf(Attachable::class);
});

it('can be converted to a mail attachment', function () {
    $attachment = Pdf::view('test')
        ->name('invoice.pdf')
        ->toMailAttachment();

    expect($attachment)
        ->toBeInstanceOf(Attachment::class)
        ->and($attachment->as)->toBe('invoice.pdf')
        ->and($attachment->mime)->toBe('application/pdf');
});

it('adds the pdf extension to the mail attachment filename when missing', function () {
    $attachment = Pdf::view('test')
        ->name('invoice')
        ->toMailAttachment();

    expect($attachment->as)->toBe('invoice.pdf');
});

it('generates pdf content when the attachment is resolved', function () {
    $pdfContent = null;

    Pdf::view('test')
        ->name('invoice.pdf')
        ->toMailAttachment()
        ->attachWith(
            fn () => null,
            function (Closure $data) use (&$pdfContent) {
                $pdfContent = $data();
            },
        );

    expect($pdfContent)
        ->toBeString()
        ->toStartWith('%PDF-');
});

it('can be used as a mail attachment on a PdfBuilder instance', function () {
    $builder = Pdf::view('test')->name('invoice.pdf');

    expect($builder)->toBeInstanceOf(PdfBuilder::class);

    $attachment = $builder->toMailAttachment();

    expect($attachment->as)->toBe('invoice.pdf');
});

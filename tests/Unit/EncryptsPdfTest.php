<?php

use Illuminate\Support\Facades\Storage;
use Spatie\LaravelPdf\Encryption\PdfEncrypter;
use Spatie\LaravelPdf\Encryption\PdfEncryption;
use Spatie\LaravelPdf\Enums\Permission;
use Spatie\LaravelPdf\Exceptions\CouldNotDecryptPdf;
use Spatie\LaravelPdf\Facades\Pdf;
use Spatie\LaravelPdf\Jobs\GeneratePdfJob;
use Spatie\LaravelPdf\PdfOptions;

it('encrypts a saved pdf', function () {
    $path = getTempPath('protected.pdf');

    Pdf::html('<h1>Hello</h1>')
        ->driver('dompdf')
        ->encrypt('secret')
        ->save($path);

    expect(file_get_contents($path))->toContain('/Encrypt');
});

it('encrypts a pdf returned as a response', function () {
    $response = Pdf::html('<h1>Hello</h1>')
        ->driver('dompdf')
        ->encrypt('secret')
        ->toResponse(request());

    expect($response->getContent())->toContain('/Encrypt');
});

it('encrypts a pdf saved to a disk', function () {
    Storage::fake('protected');

    Pdf::html('<h1>Hello</h1>')
        ->driver('dompdf')
        ->disk('protected')
        ->encrypt('secret')
        ->save('invoice.pdf');

    expect(Storage::disk('protected')->get('invoice.pdf'))->toContain('/Encrypt');
});

it('keeps metadata readable through encryption', function () {
    $path = getTempPath('protected-meta.pdf');

    Pdf::html('<h1>Hello</h1>')
        ->driver('dompdf')
        ->meta(title: 'Secret Invoice')
        ->encrypt('secret')
        ->save($path);

    $decrypted = app(PdfEncrypter::class)->decrypt(file_get_contents($path), 'secret');

    expect($decrypted)->toContain('Secret Invoice');
});

it('carries the encryption settings into a queued job', function () {
    Pdf::fake();

    Pdf::html('<h1>Hello</h1>')
        ->encrypt('secret', permissions: [Permission::Print])
        ->saveQueued('queued.pdf');

    Pdf::assertQueued(function ($pdf) {
        return $pdf->encryption?->userPassword === 'secret'
            && $pdf->encryption->permissions === [Permission::Print];
    });
});

it('encrypts a queued pdf', function () {
    $path = getTempPath('queued-protected.pdf');

    $options = new PdfOptions;
    $options->encryption = new PdfEncryption('secret');

    (new GeneratePdfJob(
        html: '<h1>Hello</h1>',
        headerHtml: null,
        footerHtml: null,
        options: $options,
        path: $path,
        driverName: 'dompdf',
    ))->handle();

    expect(file_get_contents($path))->toContain('/Encrypt');
});

it('uses a custom bound encrypter', function () {
    app()->bind(PdfEncrypter::class, fn () => new class implements PdfEncrypter
    {
        public function encrypt(string $pdf, PdfEncryption $encryption): string
        {
            return 'custom-encrypted';
        }

        public function decrypt(string $pdf, #[\SensitiveParameter] string $password): string
        {
            return 'custom-decrypted';
        }
    });

    $path = getTempPath('custom.pdf');

    Pdf::html('<h1>Hello</h1>')
        ->driver('dompdf')
        ->encrypt('secret')
        ->save($path);

    expect(file_get_contents($path))->toBe('custom-encrypted');
});

it('decrypts a pdf given its raw contents', function () {
    $path = getTempPath('to-decrypt.pdf');

    Pdf::html('<h1>Hello</h1>')
        ->driver('dompdf')
        ->encrypt('secret')
        ->save($path);

    $decrypted = Pdf::decrypt(file_get_contents($path), 'secret');

    expect($decrypted)
        ->toStartWith('%PDF-')
        ->not->toContain('/Encrypt');
});

it('decrypts a pdf given a file path', function () {
    $path = getTempPath('to-decrypt-path.pdf');

    Pdf::html('<h1>Hello</h1>')
        ->driver('dompdf')
        ->encrypt('secret')
        ->save($path);

    $decrypted = Pdf::decrypt($path, 'secret');

    expect($decrypted)
        ->toStartWith('%PDF-')
        ->not->toContain('/Encrypt');
});

it('throws when decrypting a path that does not exist', function () {
    Pdf::decrypt('/this/file/does/not/exist.pdf', 'secret');
})->throws(CouldNotDecryptPdf::class);

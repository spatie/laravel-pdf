<?php

use Dompdf\Dompdf;
use Spatie\LaravelPdf\Encryption\DefaultPdfEncrypter;
use Spatie\LaravelPdf\Encryption\PdfEncryption;
use Spatie\LaravelPdf\Enums\Permission;
use Spatie\LaravelPdf\Exceptions\CouldNotEncryptPdf;

function samplePdf(string $body = '<h1>Hello</h1><p>Visit <a href="https://spatie.be">spatie</a></p>'): string
{
    $dompdf = new Dompdf;
    $dompdf->loadHtml($body);
    $dompdf->render();

    return $dompdf->output();
}

function permissionValue(string $pdf): int
{
    expect(preg_match('/\/Filter \/Standard.*?\/P\s+(-?\d+)/s', $pdf, $matches))->toBe(1);

    return (int) $matches[1];
}

it('adds a standard encryption dictionary to the pdf', function () {
    $encrypted = (new DefaultPdfEncrypter)->encrypt(samplePdf(), new PdfEncryption('secret'));

    expect($encrypted)
        ->toStartWith('%PDF-')
        ->toContain('/Encrypt')
        ->toContain('/Filter /Standard')
        ->toContain('/V 5')
        ->toContain('/R 6');
});

it('hides plaintext strings once the pdf is encrypted', function () {
    $encrypted = (new DefaultPdfEncrypter)->encrypt(samplePdf(), new PdfEncryption('secret'));

    expect($encrypted)->not->toContain('https://spatie.be');
});

it('restores the original content when decrypted with the user password', function () {
    $encrypter = new DefaultPdfEncrypter;

    $encrypted = $encrypter->encrypt(samplePdf(), new PdfEncryption('secret'));
    $decrypted = $encrypter->decrypt($encrypted, 'secret');

    expect($decrypted)
        ->toStartWith('%PDF-')
        ->not->toContain('/Encrypt')
        ->toContain('https://spatie.be');
});

it('can decrypt using the owner password', function () {
    $encrypter = new DefaultPdfEncrypter;

    $encrypted = $encrypter->encrypt(samplePdf(), new PdfEncryption('user-secret', 'owner-secret'));

    expect($encrypter->decrypt($encrypted, 'owner-secret'))->toContain('https://spatie.be');
});

it('throws when decrypting with the wrong password', function () {
    $encrypter = new DefaultPdfEncrypter;

    $encrypted = $encrypter->encrypt(samplePdf(), new PdfEncryption('secret'));

    $encrypter->decrypt($encrypted, 'wrong-password');
})->throws(CouldNotEncryptPdf::class);

it('grants every permission by default', function () {
    $encrypted = (new DefaultPdfEncrypter)->encrypt(samplePdf(), new PdfEncryption('secret'));

    expect(permissionValue($encrypted))->toBe(2147422012);
});

it('only grants the permissions that are passed', function () {
    $encrypted = (new DefaultPdfEncrypter)->encrypt(
        samplePdf(),
        new PdfEncryption('secret', permissions: [Permission::Print]),
    );

    $allGranted = 2147422012;

    expect(permissionValue($encrypted))
        ->toBeLessThan($allGranted)
        ->and(permissionValue($encrypted) & 4)->toBe(4);
});

it('throws for pdfs that use compressed object streams', function () {
    $objectStreamPdf = "%PDF-1.5\n1 0 obj\n<< /Type /ObjStm /N 1 /First 4 >>\nstream\n1 0\nendstream\nendobj\n";

    (new DefaultPdfEncrypter)->encrypt($objectStreamPdf, new PdfEncryption('secret'));
})->throws(CouldNotEncryptPdf::class);

<?php

use Spatie\Image\Image;
use Spatie\LaravelPdf\Tests\TestCase;
use Spatie\TemporaryDirectory\TemporaryDirectory;

use function Spatie\Snapshots\assertMatchesImageSnapshot;

uses(TestCase::class)
    ->beforeAll(function () {
        (new TemporaryDirectory(getTempPath()))->delete();
    })
    ->beforeEach(function () {
        ray()->newScreen($this->name());

        $this
            ->tempDir = (new TemporaryDirectory(getTestSupportPath()))
            ->name('temp')
            ->force()
            ->create();
    })
    ->in(__DIR__);

function getTestSupportPath($suffix = ''): string
{
    return __DIR__."/TestSupport/{$suffix}";
}

function getTempPath($suffix = ''): string
{
    return getTestSupportPath('temp/'.$suffix);
}

function assertMatchesPdfSnapshot(string $pdfPath): void
{
    $imagePath = convertPdfToImage($pdfPath);

    assertMatchesImageSnapshot($imagePath, 0.5);
}



expect()->extend('toHaveDimensions', function (int $width, int $height) {
    $imagePath = convertPdfToImage($this->value);

    $image = Image::load($imagePath);

    expect($image->getWidth())->toBe(
        $width,
        "Expected width {$width} but got {$image->getWidth()}",
    );

    expect($image->getHeight())->toBe(
        $height,
        "Expected height {$height} but got {$image->getHeight()}",
    );
});

expect()->extend('toContainText', function (string $expectedText) {
    $binPath = PHP_OS === 'Linux'
        ?  '/usr/bin/pdftotext'
        : '/opt/homebrew/bin/pdftotext';

    $actualText = \Spatie\PdfToText\Pdf::getText($this->value, $binPath);

    expect(str_contains($actualText, $expectedText))->toBeTrue(
        "Expected text `{$expectedText}` not found in `{$actualText}`"
    );
});

function convertPdfToImage(string $pdfPath): string
{
    $imagePath = getTempPath('test'.'.png');

    $imagick = new Imagick($pdfPath);

    $imagick->setImageFormat('png');
    file_put_contents($imagePath, $imagick);

    return $imagePath;
}

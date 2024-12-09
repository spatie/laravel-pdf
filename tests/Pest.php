<?php

use Spatie\Image\Image;
use Spatie\LaravelPdf\Tests\TestCase;
use Spatie\PdfToText\Pdf;
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

registerSpatiePestHelpers();

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
    expect($image->getWidth())->toBeWithinRange($width - 2, $width + 2);
    expect($image->getHeight())->toBeWithinRange($height - 2, $height + 2);
});

expect()->extend('toBeWithinRange', function (int $min, int $max) {
    return $this->toBeGreaterThanOrEqual($min)
        ->toBeLessThanOrEqual($max);
});

expect()->extend('toContainText', function (string|array $expectedText) {
    $binPath = PHP_OS === 'Linux'
        ? '/usr/bin/pdftotext'
        : '/opt/homebrew/bin/pdftotext';

    $path = $this->value;

    $actualText = Pdf::getText($path, $binPath);

    if (is_string($expectedText)) {
        $expectedText = [$expectedText];
    }

    $actualText = strtolower(preg_replace('/\s+/', '', $actualText));

    foreach ($expectedText as $singleText) {
        $singleText = strtolower(preg_replace('/\s+/', '', $singleText));

        expect(str_contains($actualText, $singleText))->toBeTrue(
            "Expected text `{$singleText}` not found in `{$actualText}`"

        );
    }
});

expect()->extend('toHavePageCount', function (int $expectedNumberOfPages) {
    $image = new Imagick;
    $image->pingImage($this->value);

    expect($image->getNumberImages())->toBe($expectedNumberOfPages);
});

function convertPdfToImage(string $pdfPath): string
{
    $imagePath = getTempPath('test'.'.png');

    $imagick = new Imagick($pdfPath);

    $imagick->setImageFormat('png');
    file_put_contents($imagePath, $imagick);

    return $imagePath;
}

<?php

use Spatie\Image\Image;
use Spatie\LaravelPdf\Drivers\PdfDriver;
use Spatie\LaravelPdf\PdfFactory;
use Spatie\LaravelPdf\PdfOptions;
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

        PdfFactory::resetDefaultBuilder();

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

function retryOnFlake(callable $callback, int $tries = 3): void
{
    for ($attempt = 1; $attempt <= $tries; $attempt++) {
        try {
            $callback();

            return;
        } catch (Throwable $exception) {
            if ($attempt === $tries) {
                throw $exception;
            }

            usleep(200_000);
        }
    }
}

function convertPdfToImage(string $pdfPath): string
{
    $imagePath = getTempPath('test'.'.png');

    $imagick = new Imagick($pdfPath);

    $imagick->setImageFormat('png');
    file_put_contents($imagePath, $imagick);

    return $imagePath;
}

function fakePdfDriver(string|Throwable $output): PdfDriver
{
    return new class($output) implements PdfDriver
    {
        public int $generateCalls = 0;

        public int $saveCalls = 0;

        public function __construct(public string|Throwable $output) {}

        public function generatePdf(string $html, ?string $headerHtml, ?string $footerHtml, PdfOptions $options): string
        {
            $this->generateCalls++;

            if ($this->output instanceof Throwable) {
                throw $this->output;
            }

            return $this->output;
        }

        public function savePdf(string $html, ?string $headerHtml, ?string $footerHtml, PdfOptions $options, string $path): void
        {
            $this->saveCalls++;

            if ($this->output instanceof Throwable) {
                throw $this->output;
            }

            file_put_contents($path, $this->output);
        }
    };
}

function bindFakeDriver(string $name, PdfDriver $driver): void
{
    app()->instance("laravel-pdf.driver.{$name}", $driver);
}

function forgetPdfDriverInstances(): void
{
    foreach (['browsershot', 'cloudflare', 'dompdf', 'gotenberg', 'weasyprint', 'chrome'] as $name) {
        app()->forgetInstance("laravel-pdf.driver.{$name}");
    }

    app()->forgetInstance(PdfDriver::class);
}

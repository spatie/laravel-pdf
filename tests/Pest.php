<?php

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
    $imagePath = getTempPath('test'.'.png');

    $imagick = new Imagick($pdfPath);

    $imagick->setImageFormat('png');
    file_put_contents($imagePath, $imagick);

    assertMatchesImageSnapshot($imagePath, 0.9);
}

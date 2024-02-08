<?php

use Illuminate\Support\Str;
use Spatie\LaravelPdf\Support\PdfImageAsset;

beforeEach(function () {
    $this->targetPath = getTempPath('test.pdf');
});

it('can create a base 64 image based on a image url address', function () {
    $imageAddress = 'https://avatars.githubusercontent.com/u/7535935?s=200&v=4';
    $imageAsBase64 = PdfImageAsset::make($imageAddress);

    expect($imageAddress)
        ->toBeString()
        ->and(Str::of($imageAsBase64)
        ->startsWith('data:image/png;base64,'))
        ->toBeTrue();
});




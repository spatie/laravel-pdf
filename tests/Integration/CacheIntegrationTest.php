<?php

use Illuminate\Support\Facades\Cache;
use Spatie\LaravelPdf\Facades\Pdf;

beforeEach(function () {
    Cache::flush();
});

it('serves identical bytes from the cache on the second render', function () {
    retryOnFlake(function () {
        $first = Pdf::view('test')->cache()->base64();
        $second = Pdf::view('test')->cache()->base64();

        expect($second)->toBe($first)
            ->and(base64_decode($first))->toStartWith('%PDF');
    });
});

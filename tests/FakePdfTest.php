<?php

use Spatie\LaravelPdf\Facades\Pdf;

it('can determine the view that was used', function() {
    Pdf::fake();

    Pdf::view('test');

    Pdf::assertViewIs('test');
});

<?php

use Spatie\LaravelPdf\Support\DummyPdf;

it('generates a tiny pdf', function () {
    expect(strlen(DummyPdf::content()))->toBeLessThan(500);
});

it('generates a pdf with a header and an end of file marker', function () {
    expect(DummyPdf::content())
        ->toStartWith("%PDF-1.7\n")
        ->toEndWith("%%EOF\n");
});

it('generates a pdf with a single page', function () {
    expect(DummyPdf::content())
        ->toContain('/Type /Catalog')
        ->toContain('/Type /Pages')
        ->toContain('/Count 1')
        ->toContain('/Type /Page /Parent 2 0 R');
});

it('points the trailer to the cross reference table', function () {
    $pdf = DummyPdf::content();

    preg_match('/startxref\n(\d+)\n/', $pdf, $matches);

    expect(substr($pdf, (int) $matches[1], 4))->toBe('xref');
});

it('points every cross reference table entry to its object', function () {
    $pdf = DummyPdf::content();

    preg_match_all('/^(\d{10}) 00000 n $/m', $pdf, $matches);

    expect($matches[1])->toHaveCount(3);

    foreach ($matches[1] as $index => $offset) {
        $objectNumber = $index + 1;

        expect(substr($pdf, (int) $offset, strlen("{$objectNumber} 0 obj")))->toBe("{$objectNumber} 0 obj");
    }
});

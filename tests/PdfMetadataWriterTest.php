<?php

use Spatie\LaravelPdf\PdfMetadata;
use Spatie\LaravelPdf\PdfMetadataWriter;

/**
 * Builds a minimal but structurally valid PDF that uses a classic
 * cross-reference table followed by a `trailer` dictionary (PDF 1.4 style).
 */
function pdfWithClassicXrefTable(int $objectCount): string
{
    $pdf = "%PDF-1.4\n";
    $offsets = [0];

    for ($i = 1; $i < $objectCount; $i++) {
        $offsets[$i] = strlen($pdf);
        $pdf .= "{$i} 0 obj\n<< /Type /Filler /N {$i} >>\nendobj\n";
    }

    $xrefOffset = strlen($pdf);

    $pdf .= "xref\n";
    $pdf .= "0 {$objectCount}\n";
    $pdf .= "0000000000 65535 f \n";

    for ($i = 1; $i < $objectCount; $i++) {
        $pdf .= sprintf("%010d 00000 n \n", $offsets[$i]);
    }

    $pdf .= "trailer\n";
    $pdf .= "<< /Size {$objectCount} /Root 1 0 R >>\n";
    $pdf .= "startxref\n{$xrefOffset}\n%%EOF\n";

    return $pdf;
}

/**
 * Builds a minimal PDF that uses a cross-reference stream (PDF 1.5+), the format
 * Chromium emits. /Size and /Root live inside the stream object's dictionary, and
 * a long /Index array pushes them far away from the startxref offset.
 */
function pdfWithCrossReferenceStream(int $indexPairCount): string
{
    $pdf = "%PDF-1.7\n";

    for ($i = 1; $i < 40; $i++) {
        $pdf .= "{$i} 0 obj\n<< /Type /Filler /N {$i} >>\nendobj\n";
    }

    $xrefOffset = strlen($pdf);

    $index = implode(' ', array_map(
        fn (int $i): string => "{$i} 1",
        range(0, $indexPairCount - 1),
    ));

    $streamData = str_repeat("\x01\x00\x00\x00", 60);

    $pdf .= "40 0 obj\n";
    $pdf .= '<< /Type /XRef /W [1 4 2] /Index ['.$index.'] /Length '.strlen($streamData).' /Size 41 /Root 1 0 R >>'."\n";
    $pdf .= "stream\n{$streamData}\nendstream\nendobj\n";
    $pdf .= "startxref\n{$xrefOffset}\n%%EOF\n";

    return $pdf;
}

it('writes metadata into a pdf that uses a classic xref table', function (int $objectCount) {
    $pdf = pdfWithClassicXrefTable($objectCount);

    $result = PdfMetadataWriter::write($pdf, new PdfMetadata(title: 'My title', author: 'My author'));

    expect($result)
        ->toStartWith($pdf)
        ->toContain('/Title (My title)')
        ->toContain('/Author (My author)')
        ->toContain('/Root 1 0 R')
        ->toEndWith("%%EOF\n");
})->with([
    'small table' => 20,
    // A table with 101 entries is 2 020 bytes, which no longer fits the old
    // 2 048 byte window once the `xref`/subsection header is accounted for.
    'table larger than the old 2 KB window' => 300,
]);

it('writes metadata into a pdf that uses a cross reference stream', function (int $indexPairCount) {
    $pdf = pdfWithCrossReferenceStream($indexPairCount);

    $result = PdfMetadataWriter::write($pdf, new PdfMetadata(title: 'My title'));

    expect($result)
        ->toStartWith($pdf)
        ->toContain('/Title (My title)')
        ->toContain('/Root 1 0 R')
        ->toEndWith("%%EOF\n");
})->with([
    'small dictionary' => 10,
    // Chromium 147+ emits xref stream dictionaries well past 2 KB.
    'dictionary larger than the old 2 KB window' => 350,
    'very large dictionary' => 2500,
]);

it('carries the size of the original trailer over to the info object', function () {
    $pdf = pdfWithClassicXrefTable(300);

    $result = PdfMetadataWriter::write($pdf, new PdfMetadata(title: 'My title'));

    // /Size was 300, so the info object becomes object 300 and the new /Size is 301.
    expect($result)
        ->toContain('300 0 obj')
        ->toContain('/Size 301')
        ->toContain('/Info 300 0 R');
});

it('returns the pdf untouched when there is no metadata to write', function () {
    $pdf = pdfWithCrossReferenceStream(350);

    expect(PdfMetadataWriter::write($pdf, new PdfMetadata))->toBe($pdf);
});

it('throws when the trailer cannot be located', function () {
    $pdf = "%PDF-1.7\n1 0 obj\n<< /Type /Filler >>\nendobj\nstartxref\n9\n%%EOF\n";

    PdfMetadataWriter::write($pdf, new PdfMetadata(title: 'My title'));
})->throws(RuntimeException::class, 'Could not parse PDF trailer to find /Size and /Root.');

<?php

use Spatie\LaravelPdf\PdfBuilder;
use Spatie\LaravelPdf\PdfMetadata;
use Spatie\LaravelPdf\PdfMetadataWriter;

function buildMinimalPdf(): string
{
    $header = "%PDF-1.4\n";
    $pos = strlen($header);

    $offsets = [];

    $offsets[1] = $pos;
    $obj1 = "1 0 obj\n<< /Type /Catalog /Pages 2 0 R >>\nendobj\n";
    $pos += strlen($obj1);

    $offsets[2] = $pos;
    $obj2 = "2 0 obj\n<< /Type /Pages /Kids [3 0 R] /Count 1 >>\nendobj\n";
    $pos += strlen($obj2);

    $offsets[3] = $pos;
    $obj3 = "3 0 obj\n<< /Type /Page /Parent 2 0 R /MediaBox [0 0 612 792] >>\nendobj\n";
    $pos += strlen($obj3);

    $xrefOffset = $pos;
    $xref = "xref\n0 4\n";
    $xref .= "0000000000 65535 f \n";
    $xref .= sprintf("%010d 00000 n \n", $offsets[1]);
    $xref .= sprintf("%010d 00000 n \n", $offsets[2]);
    $xref .= sprintf("%010d 00000 n \n", $offsets[3]);

    $trailer = "trailer\n<< /Size 4 /Root 1 0 R >>\n";
    $startxref = "startxref\n{$xrefOffset}\n%%EOF\n";

    return $header.$obj1.$obj2.$obj3.$xref.$trailer.$startxref;
}

it('returns unchanged content when metadata is empty', function () {
    $pdf = buildMinimalPdf();

    $metadata = new PdfMetadata;

    $result = PdfMetadataWriter::write($pdf, $metadata);

    expect($result)->toBe($pdf);
});

it('writes title metadata to pdf', function () {
    $pdf = buildMinimalPdf();

    $metadata = new PdfMetadata(title: 'Invoice #123');

    $result = PdfMetadataWriter::write($pdf, $metadata);

    expect($result)->toContain('/Title (Invoice #123)');
    expect($result)->toContain('4 0 obj');
    expect($result)->toContain('/Info 4 0 R');
    expect($result)->toContain('/Root 1 0 R');
    expect($result)->toContain('%%EOF');
});

it('writes all metadata fields', function () {
    $pdf = buildMinimalPdf();

    $metadata = new PdfMetadata(
        title: 'My Document',
        author: 'John Doe',
        subject: 'Testing',
        keywords: 'pdf, test, metadata',
        creator: 'Laravel PDF',
        creationDate: 'D:20260207120000+00\'00\'',
    );

    $result = PdfMetadataWriter::write($pdf, $metadata);

    expect($result)
        ->toContain('/Title (My Document)')
        ->toContain('/Author (John Doe)')
        ->toContain('/Subject (Testing)')
        ->toContain('/Keywords (pdf, test, metadata)')
        ->toContain('/Creator (Laravel PDF)')
        ->toContain('/CreationDate (D:20260207120000+00\'00\')');
});

it('escapes parentheses and backslashes in strings', function () {
    $pdf = buildMinimalPdf();

    $metadata = new PdfMetadata(title: 'Title (with) parens\\slash');

    $result = PdfMetadataWriter::write($pdf, $metadata);

    expect($result)->toContain('/Title (Title \\(with\\) parens\\\\slash)');
});

it('encodes non-ascii characters as utf-16be hex strings', function () {
    $pdf = buildMinimalPdf();

    $metadata = new PdfMetadata(title: "Facture n\u{00B0}42");

    $result = PdfMetadataWriter::write($pdf, $metadata);

    // Should use hex string with BOM prefix instead of literal string
    expect($result)->toContain('<FEFF');
    expect($result)->not->toContain('/Title (');
});

it('preserves the prev xref offset for incremental updates', function () {
    $pdf = buildMinimalPdf();

    // Find the original startxref value
    preg_match('/startxref\s+(\d+)\s+%%EOF/', $pdf, $matches);
    $originalXrefOffset = $matches[1];

    $metadata = new PdfMetadata(title: 'Test');

    $result = PdfMetadataWriter::write($pdf, $metadata);

    expect($result)->toContain('/Prev '.$originalXrefOffset);
});

it('increments the size in the new trailer', function () {
    $pdf = buildMinimalPdf();

    $metadata = new PdfMetadata(title: 'Test');

    $result = PdfMetadataWriter::write($pdf, $metadata);

    // Original has /Size 4, new trailer should have /Size 5
    expect($result)->toContain('/Size 5');
});

it('writes a valid xref entry for the new object', function () {
    $pdf = buildMinimalPdf();

    $metadata = new PdfMetadata(title: 'Test');

    $result = PdfMetadataWriter::write($pdf, $metadata);

    // The xref section should reference exactly 1 new object
    expect($result)->toContain("xref\n4 1\n");

    // The offset should point to where the info object starts (= length of original PDF)
    $expectedOffset = strlen($pdf);
    $formattedOffset = sprintf('%010d', $expectedOffset);
    expect($result)->toContain($formattedOffset.' 00000 n ');
});

it('only includes non-null metadata fields', function () {
    $pdf = buildMinimalPdf();

    $metadata = new PdfMetadata(title: 'Only Title');

    $result = PdfMetadataWriter::write($pdf, $metadata);

    expect($result)
        ->toContain('/Title (Only Title)')
        ->not->toContain('/Author')
        ->not->toContain('/Subject')
        ->not->toContain('/Keywords')
        ->not->toContain('/Creator')
        ->not->toContain('/CreationDate');
});

it('stores metadata via the meta method', function () {
    $builder = new PdfBuilder;

    $builder->meta(title: 'My Title', author: 'Jane Doe', keywords: 'invoice, billing');

    expect($builder->metadata)
        ->title->toBe('My Title')
        ->author->toBe('Jane Doe')
        ->keywords->toBe('invoice, billing')
        ->subject->toBeNull()
        ->creator->toBeNull()
        ->creationDate->toBeNull();
});

it('formats DateTimeInterface as a pdf date string', function () {
    $builder = new PdfBuilder;

    $date = new DateTimeImmutable('2026-03-15 14:30:00', new DateTimeZone('UTC'));

    $builder->meta(title: 'Test', creationDate: $date);

    expect($builder->metadata->creationDate)->toBe("D:20260315143000+00'00'");
});

it('formats DateTimeInterface with non-utc timezone', function () {
    $builder = new PdfBuilder;

    $date = new DateTimeImmutable('2026-06-01 09:00:00', new DateTimeZone('Europe/Brussels'));

    $builder->meta(creationDate: $date);

    expect($builder->metadata->creationDate)->toBe("D:20260601090000+02'00'");
});

it('passes string creationDate through unchanged', function () {
    $builder = new PdfBuilder;

    $builder->meta(creationDate: "D:20260101120000Z00'00'");

    expect($builder->metadata->creationDate)->toBe("D:20260101120000Z00'00'");
});

it('reports isEmpty correctly', function () {
    expect(new PdfMetadata)->isEmpty()->toBeTrue();

    expect(new PdfMetadata(title: 'Hi'))->isEmpty()->toBeFalse();
});

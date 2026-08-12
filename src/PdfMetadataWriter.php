<?php

namespace Spatie\LaravelPdf;

use RuntimeException;

class PdfMetadataWriter
{
    public static function write(string $pdfContent, PdfMetadata $metadata): string
    {
        if ($metadata->isEmpty()) {
            return $pdfContent;
        }

        $startxrefOffset = self::findStartxrefOffset($pdfContent);
        $trailerInfo = self::parseTrailer($pdfContent, $startxrefOffset);

        $newObjectNumber = $trailerInfo['size'];
        $infoObjectOffset = strlen($pdfContent);

        $infoObject = self::buildInfoObject($newObjectNumber, $metadata);

        $xrefOffset = $infoObjectOffset + strlen($infoObject);

        $xref = "xref\n";
        $xref .= "{$newObjectNumber} 1\n";
        $xref .= sprintf("%010d 00000 n \n", $infoObjectOffset);

        $trailer = "trailer\n";
        $trailer .= '<< /Size '.($newObjectNumber + 1);
        $trailer .= ' /Root '.$trailerInfo['root'];
        $trailer .= ' /Info '.$newObjectNumber.' 0 R';
        $trailer .= ' /Prev '.$startxrefOffset;
        $trailer .= " >>\n";

        $footer = "startxref\n";
        $footer .= $xrefOffset."\n";
        $footer .= "%%EOF\n";

        return $pdfContent.$infoObject.$xref.$trailer.$footer;
    }

    protected static function findStartxrefOffset(string $pdfContent): int
    {
        $tail = substr($pdfContent, -1024);

        if (preg_match('/startxref\s+(\d+)\s+%%EOF/', $tail, $matches)) {
            return (int) $matches[1];
        }

        throw new RuntimeException('Could not find startxref in PDF content.');
    }

    protected static function parseTrailer(string $pdfContent, int $startxrefOffset): array
    {
        $dictionary = self::extractTrailerDictionary($pdfContent, $startxrefOffset);

        if ($dictionary === null
            || ! preg_match('/\/Size\s+(\d+)/', $dictionary, $sizeMatch)
            || ! preg_match('/\/Root\s+(\d+\s+\d+\s+R)/', $dictionary, $rootMatch)) {
            throw new RuntimeException('Could not parse PDF trailer to find /Size and /Root.');
        }

        return [
            'size' => (int) $sizeMatch[1],
            'root' => $rootMatch[1],
        ];
    }

    /**
     * `startxref` points at either a classic cross-reference table or at the object
     * header of a cross-reference stream (PDF 1.5+). Table entries only ever contain
     * digits, spaces and the letters `n` and `f`, so in both layouts the first `<<`
     * that follows opens the dictionary holding /Size and /Root. Nesting is tracked so
     * that dictionary ends at its matching `>>` instead of at a fixed byte count.
     */
    protected static function extractTrailerDictionary(string $pdfContent, int $startxrefOffset): ?string
    {
        $start = strpos($pdfContent, '<<', $startxrefOffset);

        if ($start === false) {
            return null;
        }

        $depth = 0;
        $offset = $start;

        while (preg_match('/<<|>>/', $pdfContent, $matches, PREG_OFFSET_CAPTURE, $offset)) {
            [$token, $position] = $matches[0];

            $depth += $token === '<<' ? 1 : -1;
            $offset = $position + 2;

            if ($depth === 0) {
                return substr($pdfContent, $start, $offset - $start);
            }
        }

        return null;
    }

    protected static function buildInfoObject(int $objectNumber, PdfMetadata $metadata): string
    {
        $entries = [];

        if ($metadata->title !== null) {
            $entries[] = '/Title '.self::encodeString($metadata->title);
        }

        if ($metadata->author !== null) {
            $entries[] = '/Author '.self::encodeString($metadata->author);
        }

        if ($metadata->subject !== null) {
            $entries[] = '/Subject '.self::encodeString($metadata->subject);
        }

        if ($metadata->keywords !== null) {
            $entries[] = '/Keywords '.self::encodeString($metadata->keywords);
        }

        if ($metadata->creator !== null) {
            $entries[] = '/Creator '.self::encodeString($metadata->creator);
        }

        if ($metadata->creationDate !== null) {
            $entries[] = '/CreationDate '.self::encodeString($metadata->creationDate);
        }

        $dict = implode(' ', $entries);

        return "{$objectNumber} 0 obj\n<< {$dict} >>\nendobj\n";
    }

    protected static function encodeString(string $value): string
    {
        if (preg_match('/[^\x20-\x7E]/', $value)) {
            $utf16 = mb_convert_encoding($value, 'UTF-16BE', 'UTF-8');

            return '<FEFF'.strtoupper(bin2hex($utf16)).'>';
        }

        $escaped = str_replace('\\', '\\\\', $value);
        $escaped = str_replace('(', '\\(', $escaped);
        $escaped = str_replace(')', '\\)', $escaped);

        return '('.$escaped.')';
    }
}

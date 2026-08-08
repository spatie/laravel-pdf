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
     * The bytes at the startxref offset are either the `xref` keyword of a classic
     * cross-reference table, or the `N G obj` header of a cross-reference stream
     * (PDF 1.5+). Both eventually lead to a dictionary holding /Size and /Root.
     */
    protected static function extractTrailerDictionary(string $pdfContent, int $startxrefOffset): ?string
    {
        if (substr($pdfContent, $startxrefOffset, 4) !== 'xref') {
            // Cross-reference stream: /Size and /Root live in the stream object's dictionary.
            return self::extractDictionary($pdfContent, $startxrefOffset);
        }

        // Classic cross-reference table. Its entries only ever contain digits, spaces
        // and the letters `n` and `f`, so the first `trailer` keyword after the table
        // starts is always the trailer we are looking for, no matter how many
        // subsections or entries the table has.
        $trailerPosition = strpos($pdfContent, 'trailer', $startxrefOffset);

        if ($trailerPosition === false) {
            return null;
        }

        return self::extractDictionary($pdfContent, $trailerPosition);
    }

    /**
     * Return the dictionary that starts at the first `<<` at or after the given
     * position, tracking nesting so we stop at the matching `>>` instead of at a
     * fixed byte count.
     */
    protected static function extractDictionary(string $pdfContent, int $searchFrom): ?string
    {
        $start = strpos($pdfContent, '<<', $searchFrom);

        if ($start === false) {
            return null;
        }

        $depth = 0;
        $position = $start;
        $length = strlen($pdfContent);

        while ($position < $length - 1) {
            $token = substr($pdfContent, $position, 2);

            if ($token === '<<') {
                $depth++;
                $position += 2;

                continue;
            }

            if ($token === '>>') {
                $depth--;
                $position += 2;

                if ($depth === 0) {
                    return substr($pdfContent, $start, $position - $start);
                }

                continue;
            }

            $position++;
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

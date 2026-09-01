<?php

namespace Spatie\LaravelPdf\Support;

class DummyPdf
{
    public static function content(): string
    {
        $objects = [
            '<< /Type /Catalog /Pages 2 0 R >>',
            '<< /Type /Pages /Kids [3 0 R] /Count 1 >>',
            '<< /Type /Page /Parent 2 0 R /MediaBox [0 0 595 842] /Resources << >> >>',
        ];

        $pdf = "%PDF-1.7\n";

        $objectOffsets = [];

        foreach ($objects as $index => $object) {
            $objectOffsets[] = strlen($pdf);

            $objectNumber = $index + 1;

            $pdf .= "{$objectNumber} 0 obj\n{$object}\nendobj\n";
        }

        $crossReferenceTableOffset = strlen($pdf);

        $size = count($objects) + 1;

        $pdf .= "xref\n0 {$size}\n";
        $pdf .= "0000000000 65535 f \n";

        foreach ($objectOffsets as $objectOffset) {
            $pdf .= sprintf("%010d 00000 n \n", $objectOffset);
        }

        $pdf .= "trailer\n<< /Size {$size} /Root 1 0 R >>\n";
        $pdf .= "startxref\n{$crossReferenceTableOffset}\n";
        $pdf .= "%%EOF\n";

        return $pdf;
    }
}

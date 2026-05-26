<?php

namespace Spatie\LaravelPdf\PostProcessing;

interface PdfPostProcessor
{
    public function process(string $pdf): string;
}

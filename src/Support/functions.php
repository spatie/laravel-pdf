<?php

namespace Spatie\LaravelPdf\Support;

use Spatie\LaravelPdf\Facades\Pdf;
use Spatie\LaravelPdf\PdfBuilder;

function pdf(string $viewPath = '', array $data = []): PdfBuilder
{
    return Pdf::view($viewPath, $data);
}

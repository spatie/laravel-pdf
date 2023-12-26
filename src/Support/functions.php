<?php

namespace Spatie\LaravelPdf\Support;

use Spatie\LaravelPdf\Pdf;

function pdf(string $viewPath, array $data = []): Pdf
{
    return \Spatie\LaravelPdf\Facades\Pdf::view($viewPath, $data);
}

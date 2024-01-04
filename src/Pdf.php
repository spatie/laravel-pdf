<?php

namespace Spatie\LaravelPdf;

use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Contracts\Support\Responsable;
use Illuminate\Support\Facades\Blade;
use Spatie\LaravelPdf\Facades\Pdf as Builder;
use Illuminate\View\Component;

abstract class Pdf extends Component implements Responsable, Htmlable
{
    public function getPdfBuilder(): PdfBuilder
    {
        return Builder::html($this->toHtml());
    }

    public function toHtml()
    {
        return Blade::renderComponent($this);
    }

    public function toResponse($request)
    {
        return $this->getPdfBuilder()->toResponse($request);
    }
}

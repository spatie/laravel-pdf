<?php

namespace Spatie\LaravelPdf;

use Spatie\LaravelPdf\DNS1D;

class BarcodeBuilder
{
    protected DNS1D $dns1d;

    public function __construct()
    {
        $this->dns1d = new DNS1D();
    }

    public function generate(string $text, string $format = 'C39'): string
    {
        return $this->dns1d->getBarcodeHTML($text, $format);
    }
}
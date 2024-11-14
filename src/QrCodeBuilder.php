<?php

namespace Spatie\LaravelPdf;

use Spatie\LaravelPdf\DNS2D;

class QrCodeBuilder
{
    protected DNS2D $dns2d;

    public function __construct()
    {
        $this->dns2d = new DNS2D();
    }

    public function generate(string $text, string $format = 'QRCODE'): string
    {
        return $this->dns2d->getBarcodeHTML($text, $format);
    }
}
<?php

namespace Spatie\LaravelPdf\Enums;

use Spatie\LaravelPdf\Values\Length;

enum Format: string
{
    case Letter = 'letter';
    case Legal = 'legal';
    case Tabloid = 'tabloid';
    case Ledger = 'ledger';
    case A0 = 'a0';
    case A1 = 'a1';
    case A2 = 'a2';
    case A3 = 'a3';
    case A4 = 'a4';
    case A5 = 'a5';
    case A6 = 'a6';

    public function width(): Length {
        [$value, $unit] = config("pdf.paperSizes.{$this->value}.width");

        return Length::make($value, $unit);
    }

    public function height(): Length {
        [$value, $unit] = config("pdf.paperSizes.{$this->value}.height");

        return Length::make($value, $unit);
    }
}

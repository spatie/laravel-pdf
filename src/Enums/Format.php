<?php

namespace Spatie\LaravelPdf\Enums;

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

    public function width(): float {
        return match ($this) {
            Format::Letter => 215.9,
            Format::Legal => 215.19,
            Format::Tabloid => 279,
            Format::Ledger => 432,
            Format::A0 => 841,
            Format::A1 => 594,
            Format::A2 => 420,
            Format::A3 => 297,
            Format::A4 => 210,
            Format::A5 => 148,
            Format::A6 => 105,
        };
    }

    public function height(): float {
        return match ($this) {
            Format::Letter => 215.9,
            Format::Legal => 355.6,
            Format::Tabloid => 432,
            Format::Ledger => 279,
            Format::A0 => 1189,
            Format::A1 => 841,
            Format::A2 => 594,
            Format::A3 => 420,
            Format::A4 => 297,
            Format::A5 => 210,
            Format::A6 => 148,
        };
    }
}

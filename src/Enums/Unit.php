<?php

namespace Spatie\LaravelPdf\Enums;

enum Unit: string
{
    case Pixel = 'px';
    case Inch = 'in';
    case Centimeter = 'cm';
    case Millimeter = 'mm';

    public function toMillimeter($value): float {
        return match($this) {
            Unit::Pixel => $value * 0.2645833333,
            Unit::Inch => $value * 25.4,
            Unit::Centimeter => $value * 10,
            Unit::Millimeter => $value
        };
    }
}

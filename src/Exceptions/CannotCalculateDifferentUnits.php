<?php

namespace Spatie\LaravelPdf\Exceptions;

use Exception;

class CannotCalculateDifferentUnits extends Exception
{
    public static function new(): DetectedOverflowingMargins
    {
        return new self('Different units cannot be calculated.');
    }
}
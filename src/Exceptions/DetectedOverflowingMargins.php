<?php

namespace Spatie\LaravelPdf\Exceptions;

use Exception;

class DetectedOverflowingMargins extends Exception
{
    public static function marginIsGreaterThanWidth(): DetectedOverflowingMargins
    {
        return new self('The defined margin is greater than the width.');
    }

    public static function marginIsGreaterThanHeight(): DetectedOverflowingMargins
    {
        return new self('The defined margin is greater than the height.');
    }
}
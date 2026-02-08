<?php

namespace Spatie\LaravelPdf\Exceptions;

use Exception;

class InvalidDriver extends Exception
{
    public static function unknown(string $driverName): self
    {
        return new self("Unknown PDF driver [{$driverName}].");
    }
}

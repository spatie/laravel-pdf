<?php

namespace Spatie\LaravelPdf\Exceptions;

use Exception;

class ValidationException extends Exception
{
    public static function invalidFormat($candidate): ValidationException
    {
        return new self("Format {$candidate} is not supported.");
    }

    public static function invalidUnit($attribute, $candidate): ValidationException
    {
        return new self("Unit {$candidate} for {$attribute} is not supported.");
    }
}
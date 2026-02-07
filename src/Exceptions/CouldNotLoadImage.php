<?php

namespace Spatie\LaravelPdf\Exceptions;

use Exception;

class CouldNotLoadImage extends Exception
{
    public static function notFound(string $message): self
    {
        return new self("Image not found: {$message}");
    }

    public static function fetchFailed(string $error): self
    {
        return new self("Failed to fetch the image: {$error}");
    }
}

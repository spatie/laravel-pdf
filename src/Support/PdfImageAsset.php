<?php

namespace Spatie\LaravelPdf\Support;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use RuntimeException;

class PdfImageAsset
{
    public static function make(string $path): string
    {
        if (!Str::of($path)->isUrl()) {
            return 'data:image/png;base64,' . base64_encode(file_get_contents($path));
        }

        $response = Http::get($path);

        if ($response->successful()) {
            $imageContent = $response->body();

            return 'data:image/png;base64,' . base64_encode($imageContent);
        }

        throw new RuntimeException('Failed to fetch the image');
    }
}

<?php

namespace Spatie\LaravelPdf;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use RuntimeException;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class PdfServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package->name('laravel-pdf');
    }

    public function bootingPackage()
    {
        Blade::directive('pageBreak', function () {
            return "<?php echo '<div style=\"page-break-after: always;\"></div>'; ?>";
        });

        Blade::directive('pageNumber', function () {
            return "<?php echo '<span class=\"pageNumber\"></span>'; ?>";
        });

        Blade::directive('totalPages', function () {
            return "<?php echo '<span class=\"totalPages\"></span>'; ?>";
        });

        Blade::directive('inlinedImage', function ($url) {
            $url = Str::of($url)->trim("'")->trim('"')->value();

            if (! Str::of($url)->isUrl()) {
                $imageContent = 'data:image/png;base64,'.base64_encode(file_get_contents(public_path($url)));

                return "<?php echo '<img src=\"$imageContent\">'; ?>";
            }

            $response = Http::get($url);

            if ($response->successful()) {
                $imageContent = 'data:image/png;base64,'.base64_encode($response->body());

                return "<?php echo '<img src=\"$imageContent\">'; ?>";
            }

            throw new RuntimeException('Failed to fetch the image');
        });

    }
}

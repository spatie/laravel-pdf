<?php

namespace Spatie\LaravelPdf;

use Illuminate\Support\Facades\Blade;
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
            return "<?php
                \$url = \Illuminate\Support\Str::of($url)->trim(\"'\")->trim('\"')->value();

                if (! \Illuminate\Support\Str::of(\$url)->isUrl()) {
                    try {
                        \$imageContent = 'data:image/png;base64,' . base64_encode(file_get_contents(\$url));
                        echo '<img src=\"' . \$imageContent . '\">';
                    } catch(\Exception \$exception) {
                        throw new \Illuminate\View\ViewException('Image not found: ' . \$exception->getMessage());
                    }
                } else {
                    \$response = \Illuminate\Support\Facades\Http::get(\$url);

                    if (! \$response->successful()) {
                        throw new \Illuminate\View\ViewException('Failed to fetch the image: ' . \$response->toException());
                    }

                    \$imageContent = 'data:image/png;base64,' . base64_encode(\$response->body());
                    echo '<img src=\"' . \$imageContent . '\">';
                }
            ?>";
        });

    }
}

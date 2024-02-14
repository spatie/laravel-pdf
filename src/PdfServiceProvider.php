<?php

namespace Spatie\LaravelPdf;

use Illuminate\Support\Facades\Blade;
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
            try {
                return "<?php echo '<img src=\"'. 'data:image/png;base64,'.base64_encode(file_get_contents(asset($url))) .'\">'; ?>";
            } catch (\Exception $exception) {
                try {
                    return "<?php echo '<img src=\"' . 'data:image/png;base64,'.base64_encode(
                        Http::get($url)->throwUnlessStatus(200)->body()
                    ) . '\">'; ?>";
                } catch (\Exception $exception) {
                    throw new RuntimeException('Failed to fetch the image', $exception->getCode(), $exception);
                }
            }
        });

    }
}

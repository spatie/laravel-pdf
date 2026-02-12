<?php

namespace Spatie\LaravelPdf\Drivers;

use Pontedilana\PhpWeasyPrint\Pdf;
use Spatie\LaravelPdf\Exceptions\CouldNotGeneratePdf;
use Spatie\LaravelPdf\PdfOptions;

class WeasyPrintDriver implements PdfDriver
{
    protected array $config;

    public function __construct(array $config = [])
    {
        if (! class_exists(Pdf::class)) {
            throw CouldNotGeneratePdf::weasyPrintPackageNotInstalled();
        }

        $this->config = $config;
    }

    protected function buildWeasyPrint(): Pdf
    {
        $options = $this->config;

        $binary = $this->config['binary'] ?? null;
        unset($options['binary']);

        return new Pdf($binary, $options);
    }

    public function generatePdf(
        string $html,
        ?string $headerHtml,
        ?string $footerHtml,
        PdfOptions $options,
    ): string {
        $html = $this->mergeHeaderFooter($html, $headerHtml, $footerHtml);

        return $this->buildWeasyPrint()->getOutputFromHtml($html, $this->prepareOptions($options));
    }

    public function savePdf(
        string $html,
        ?string $headerHtml,
        ?string $footerHtml,
        PdfOptions $options,
        string $path,
    ): void {
        $html = $this->mergeHeaderFooter($html, $headerHtml, $footerHtml);

        $this->buildWeasyPrint()->generateFromHtml($html, $path, $this->prepareOptions($options), true);
    }

    protected function mergeHeaderFooter(string $html, ?string $headerHtml, ?string $footerHtml): string
    {
        if (! $headerHtml && ! $footerHtml) {
            return $html;
        }

        $headerBlock = $headerHtml
            ? '<div class="pdf-header">'.$headerHtml.'</div>'
            : '';

        $footerBlock = $footerHtml
            ? '<div class="pdf-footer">'.$footerHtml.'</div>'
            : '';

        if (preg_match('/<body([^>]*)>/i', $html, $matches)) {
            return preg_replace(
                '/<body([^>]*)>/i',
                '<body$1>'.$headerBlock.$footerBlock,
                $html,
            );
        }

        return $headerBlock.$footerBlock.$html;
    }

    protected function prepareOptions(PdfOptions $options): array
    {
        $marginCss = '';
        if ($options->margins) {
            $unit = $options->margins['unit'] ?? 'mm';
            $top = $options->margins['top'].$unit;
            $right = $options->margins['right'].$unit;
            $bottom = $options->margins['bottom'].$unit;
            $left = $options->margins['left'].$unit;

            $marginCss = "margin: {$top} {$right} {$bottom} {$left};";
        }

        $sizeCss = '';
        if ($options->paperSize) {
            $unit = $options->paperSize['unit'] ?? 'mm';
            $sizeWidth = $options->paperSize['width'].$unit;
            $sizeHeight = $options->paperSize['height'].$unit;
            $sizeCss = "size: {$sizeWidth} {$sizeHeight};";
        } elseif ($options->format || $options->orientation) {
            $format = strtolower($options->format);
            if ($options->orientation) {
                $format .= ' '.strtolower($options->orientation);
            }
            $sizeCss = "size: {$format};";
        }

        $stylesheet = <<<CSS
@page {
    {$sizeCss}
    {$marginCss}

    @top-left {
        content: element(pdfHeader);
    }
    @bottom-left {
        content: element(pdfFooter);
    }
}
.pdf-header {
    position: running(pdfHeader);
}
.pdf-footer {
    position: running(pdfFooter);
}

.pageNumber::before { content: counter(page); }
.totalPages::before { content: counter(pages); }
CSS;

        return [
            'stylesheet' => $stylesheet,
        ];
    }
}

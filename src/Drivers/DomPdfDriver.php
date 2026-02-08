<?php

namespace Spatie\LaravelPdf\Drivers;

use Dompdf\Dompdf;
use Dompdf\Options;
use Spatie\LaravelPdf\Enums\Orientation;
use Spatie\LaravelPdf\Exceptions\CouldNotGeneratePdf;
use Spatie\LaravelPdf\PdfOptions;

class DomPdfDriver implements PdfDriver
{
    protected array $config;

    public function __construct(array $config = [])
    {
        if (! class_exists(Dompdf::class)) {
            throw CouldNotGeneratePdf::dompdfNotInstalled();
        }

        $this->config = $config;
    }

    public function generatePdf(string $html, ?string $headerHtml, ?string $footerHtml, PdfOptions $options): string
    {
        $dompdf = $this->buildDompdf($html, $headerHtml, $footerHtml, $options);

        $dompdf->render();

        return $dompdf->output();
    }

    public function savePdf(string $html, ?string $headerHtml, ?string $footerHtml, PdfOptions $options, string $path): void
    {
        $pdfContent = $this->generatePdf($html, $headerHtml, $footerHtml, $options);

        file_put_contents($path, $pdfContent);
    }

    protected function buildDompdf(string $html, ?string $headerHtml, ?string $footerHtml, PdfOptions $options): Dompdf
    {
        $dompdf = new Dompdf($this->buildOptions());

        $html = $this->mergeHeaderFooter($html, $headerHtml, $footerHtml);
        $html = $this->injectMarginCss($html, $options);

        $dompdf->loadHtml($html);

        $this->applyPaper($dompdf, $options);

        return $dompdf;
    }

    protected function buildOptions(): Options
    {
        $options = new Options;

        $options->setIsRemoteEnabled($this->config['is_remote_enabled'] ?? false);

        if ($chroot = ($this->config['chroot'] ?? null)) {
            $options->setChroot($chroot);
        }

        return $options;
    }

    protected function applyPaper(Dompdf $dompdf, PdfOptions $options): void
    {
        $orientation = $options->orientation === Orientation::Landscape->value
            ? 'landscape'
            : 'portrait';

        if ($options->paperSize) {
            $widthPt = $this->toPoints($options->paperSize['width'], $options->paperSize['unit'] ?? 'mm');
            $heightPt = $this->toPoints($options->paperSize['height'], $options->paperSize['unit'] ?? 'mm');

            $dompdf->setPaper([0, 0, $widthPt, $heightPt], $orientation);

            return;
        }

        $format = $options->format ?? 'a4';

        $dompdf->setPaper($format, $orientation);
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
            $html = preg_replace(
                '/<body([^>]*)>/i',
                '<body$1>'.$headerBlock,
                $html,
            );

            $html = str_replace('</body>', $footerBlock.'</body>', $html);

            return $html;
        }

        return $headerBlock.$html.$footerBlock;
    }

    protected function injectMarginCss(string $html, PdfOptions $options): string
    {
        if (! $options->margins) {
            return $html;
        }

        $unit = $options->margins['unit'] ?? 'mm';
        $top = $options->margins['top'].$unit;
        $right = $options->margins['right'].$unit;
        $bottom = $options->margins['bottom'].$unit;
        $left = $options->margins['left'].$unit;

        $css = "<style>@page { margin: {$top} {$right} {$bottom} {$left}; }</style>";

        if (preg_match('/<head([^>]*)>/i', $html)) {
            return preg_replace('/<\/head>/i', $css.'</head>', $html);
        }

        return $css.$html;
    }

    protected function toPoints(float $value, string $unit): float
    {
        return match ($unit) {
            'mm' => $value * 2.83465,
            'cm' => $value * 28.3465,
            'in' => $value * 72,
            'px' => $value * 0.75,
            'pt' => $value,
            default => $value * 2.83465,
        };
    }
}

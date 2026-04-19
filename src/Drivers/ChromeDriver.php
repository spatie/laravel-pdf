<?php

namespace Spatie\LaravelPdf\Drivers;

use HeadlessChromium\Browser\ProcessAwareBrowser;
use HeadlessChromium\BrowserFactory;
use Spatie\LaravelPdf\Enums\Orientation;
use Spatie\LaravelPdf\Exceptions\CouldNotGeneratePdf;
use Spatie\LaravelPdf\PdfOptions;

class ChromeDriver implements PdfDriver
{
    protected array $config;

    /**
     * Paper sizes in inches [width, height].
     */
    protected const PAPER_SIZES = [
        'letter' => [8.5, 11],
        'legal' => [8.5, 14],
        'tabloid' => [11, 17],
        'ledger' => [17, 11],
        'a0' => [33.1, 46.8],
        'a1' => [23.4, 33.1],
        'a2' => [16.54, 23.4],
        'a3' => [11.7, 16.54],
        'a4' => [8.27, 11.7],
        'a5' => [5.83, 8.27],
        'a6' => [4.13, 5.83],
    ];

    /**
     * Conversion factors to inches.
     */
    protected const UNIT_TO_INCHES = [
        'mm' => 0.0393701,
        'cm' => 0.393701,
        'in' => 1.0,
        'px' => 0.0104167,
    ];

    public function __construct(array $config = [])
    {
        if (! class_exists(BrowserFactory::class)) {
            throw CouldNotGeneratePdf::chromeNotInstalled();
        }

        $this->config = $config;
    }

    public function generatePdf(string $html, ?string $headerHtml, ?string $footerHtml, PdfOptions $options): string
    {
        $browser = $this->createBrowser();

        try {
            $page = $browser->createPage();
            $page->setHtml($html, $this->config['timeout'] ?? 30000);

            $pdfOptions = $this->buildPdfOptions($headerHtml, $footerHtml, $options);
            $pdf = $page->pdf($pdfOptions);

            return $pdf->getRawBinary();
        } finally {
            $browser->close();
        }
    }

    public function savePdf(string $html, ?string $headerHtml, ?string $footerHtml, PdfOptions $options, string $path): void
    {
        $browser = $this->createBrowser();

        try {
            $page = $browser->createPage();
            $page->setHtml($html, $this->config['timeout'] ?? 30000);

            $pdfOptions = $this->buildPdfOptions($headerHtml, $footerHtml, $options);
            $page->pdf($pdfOptions)->saveToFile($path);
        } finally {
            $browser->close();
        }
    }

    protected function createBrowser(): ProcessAwareBrowser
    {
        return (new BrowserFactory($this->config['chrome_binary'] ?? null))
            ->createBrowser($this->buildBrowserOptions());
    }

    protected function buildBrowserOptions(): array
    {
        $options = [
            'headless' => true,
        ];

        if ($this->config['no_sandbox'] ?? false) {
            $options['noSandbox'] = true;
        }

        if ($startupTimeout = ($this->config['startup_timeout'] ?? null)) {
            $options['startupTimeout'] = $startupTimeout;
        }

        if ($userDataDir = ($this->config['user_data_dir'] ?? null)) {
            $options['userDataDir'] = $userDataDir;
        }

        if ($customFlags = ($this->config['custom_flags'] ?? null)) {
            $options['customFlags'] = $customFlags;
        }

        if ($envVariables = ($this->config['env_variables'] ?? null)) {
            $options['envVariables'] = $envVariables;
        }

        return $options;
    }

    protected function buildPdfOptions(?string $headerHtml, ?string $footerHtml, PdfOptions $options): array
    {
        $pdfOptions = [
            'printBackground' => true,
        ];

        if ($options->format) {
            $format = strtolower($options->format);

            if (isset(self::PAPER_SIZES[$format])) {
                [$width, $height] = self::PAPER_SIZES[$format];
                $pdfOptions['paperWidth'] = $width;
                $pdfOptions['paperHeight'] = $height;
            }
        }

        if ($options->paperSize) {
            $unit = $options->paperSize['unit'] ?? 'mm';
            $factor = self::UNIT_TO_INCHES[$unit] ?? self::UNIT_TO_INCHES['mm'];

            $pdfOptions['paperWidth'] = $options->paperSize['width'] * $factor;
            $pdfOptions['paperHeight'] = $options->paperSize['height'] * $factor;
        }

        if ($options->margins) {
            $unit = $options->margins['unit'] ?? 'mm';
            $factor = self::UNIT_TO_INCHES[$unit] ?? self::UNIT_TO_INCHES['mm'];

            $pdfOptions['marginTop'] = $options->margins['top'] * $factor;
            $pdfOptions['marginRight'] = $options->margins['right'] * $factor;
            $pdfOptions['marginBottom'] = $options->margins['bottom'] * $factor;
            $pdfOptions['marginLeft'] = $options->margins['left'] * $factor;
        }

        if ($options->orientation === Orientation::Landscape->value) {
            $pdfOptions['landscape'] = true;
        }

        if ($options->scale !== null) {
            $pdfOptions['scale'] = $options->scale;
        }

        if ($options->pageRanges !== null) {
            $pdfOptions['pageRanges'] = $options->pageRanges;
        }

        if ($headerHtml || $footerHtml) {
            $pdfOptions['displayHeaderFooter'] = true;

            if ($headerHtml) {
                $pdfOptions['headerTemplate'] = $headerHtml;
            }

            if ($footerHtml) {
                $pdfOptions['footerTemplate'] = $footerHtml;
            }
        }

        return $pdfOptions;
    }
}

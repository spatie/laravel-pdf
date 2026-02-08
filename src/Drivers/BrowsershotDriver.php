<?php

namespace Spatie\LaravelPdf\Drivers;

use Closure;
use Spatie\Browsershot\Browsershot;
use Spatie\LaravelPdf\Enums\Orientation;
use Spatie\LaravelPdf\Exceptions\CouldNotGeneratePdf;
use Spatie\LaravelPdf\PdfOptions;
use Wnx\SidecarBrowsershot\BrowsershotLambda;

class BrowsershotDriver implements PdfDriver
{
    protected array $config;

    protected ?Closure $customizeBrowsershot = null;

    protected bool $onLambda = false;

    public function __construct(array $config = [])
    {
        if (! class_exists(Browsershot::class)) {
            throw CouldNotGeneratePdf::browsershotNotInstalled();
        }

        $this->config = $config;
    }

    public function customizeBrowsershot(?Closure $callback): self
    {
        $this->customizeBrowsershot = $callback;

        return $this;
    }

    public function onLambda(bool $onLambda = true): self
    {
        $this->onLambda = $onLambda;

        return $this;
    }

    public function generatePdf(string $html, ?string $headerHtml, ?string $footerHtml, PdfOptions $options): string
    {
        $browsershot = $this->buildBrowsershot($html, $headerHtml, $footerHtml, $options);

        return $browsershot->pdf();
    }

    public function savePdf(string $html, ?string $headerHtml, ?string $footerHtml, PdfOptions $options, string $path): void
    {
        $browsershot = $this->buildBrowsershot($html, $headerHtml, $footerHtml, $options);

        $browsershot->save($path);
    }

    protected function buildBrowsershot(string $html, ?string $headerHtml, ?string $footerHtml, PdfOptions $options): Browsershot
    {
        $browsershotClass = $this->onLambda
            ? BrowsershotLambda::class
            : Browsershot::class;

        $browsershot = $browsershotClass::html($html);

        $browsershot->showBackground();

        if ($headerHtml || $footerHtml) {
            $browsershot->showBrowserHeaderAndFooter();

            if (! $headerHtml) {
                $browsershot->hideHeader();
            }

            if (! $footerHtml) {
                $browsershot->hideFooter();
            }

            if ($headerHtml) {
                $browsershot->headerHtml($headerHtml);
            }

            if ($footerHtml) {
                $browsershot->footerHtml($footerHtml);
            }
        }

        if ($options->margins) {
            $browsershot->margins(...$options->margins);
        }

        if ($options->format) {
            $browsershot->format($options->format);
        }

        if ($options->paperSize) {
            $browsershot->paperSize(...$options->paperSize);
        }

        if ($options->orientation === Orientation::Landscape->value) {
            $browsershot->landscape();
        }

        if ($options->scale !== null) {
            $browsershot->scale($options->scale);
        }

        if ($options->pageRanges !== null) {
            $browsershot->pages($options->pageRanges);
        }

        if ($options->tagged) {
            $browsershot->taggedPdf();
        }

        $this->applyConfigurationDefaults($browsershot);

        if ($this->customizeBrowsershot) {
            ($this->customizeBrowsershot)($browsershot);
        }

        return $browsershot;
    }

    protected function applyConfigurationDefaults(Browsershot $browsershot): void
    {
        if ($nodeBinary = ($this->config['node_binary'] ?? null)) {
            $browsershot->setNodeBinary($nodeBinary);
        }

        if ($npmBinary = ($this->config['npm_binary'] ?? null)) {
            $browsershot->setNpmBinary($npmBinary);
        }

        if ($includePath = ($this->config['include_path'] ?? null)) {
            $browsershot->setIncludePath($includePath);
        }

        if ($chromePath = ($this->config['chrome_path'] ?? null)) {
            $browsershot->setChromePath($chromePath);
        }

        if ($nodeModulesPath = ($this->config['node_modules_path'] ?? null)) {
            $browsershot->setNodeModulePath($nodeModulesPath);
        }

        if ($binPath = ($this->config['bin_path'] ?? null)) {
            $browsershot->setBinPath($binPath);
        }

        if ($tempPath = ($this->config['temp_path'] ?? null)) {
            $browsershot->setCustomTempPath($tempPath);
        }

        if ($this->config['write_options_to_file'] ?? false) {
            $browsershot->writeOptionsToFile();
        }

        if ($this->config['no_sandbox'] ?? false) {
            $browsershot->noSandbox();
        }
    }
}

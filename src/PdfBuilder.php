<?php

namespace Spatie\LaravelPdf;

use Closure;
use Illuminate\Contracts\Support\Responsable;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Spatie\Browsershot\Browsershot;
use Spatie\LaravelPdf\Enums\Format;
use Spatie\LaravelPdf\Enums\Orientation;
use Spatie\LaravelPdf\Enums\Unit;
use Wnx\SidecarBrowsershot\BrowsershotLambda;

class PdfBuilder implements Responsable
{
    public string $viewName = '';

    public array $viewData = [];

    public string $html = '';

    public string $headerViewName = '';

    public array $headerData = [];

    public ?string $headerHtml = null;

    public string $footerViewName = '';

    public array $footerData = [];

    public ?string $footerHtml = null;

    public string $downloadName = '';

    public ?string $format = null;

    public ?array $paperSize = null;

    public ?string $orientation = null;

    public ?array $margins = null;

    protected string $visibility = 'private';

    protected ?Closure $customizeBrowsershot = null;

    protected array $responseHeaders = [
        'Content-Type' => 'application/pdf',
    ];

    protected bool $onLambda = false;

    protected ?string $diskName = null;

    public function view(string $view, array $data = []): self
    {
        $this->viewName = $view;

        $this->viewData = $data;

        return $this;
    }

    public function headerView(string $view, array $data = []): self
    {
        $this->headerViewName = $view;

        $this->headerData = $data;

        return $this;
    }

    public function footerView(string $view, array $data = []): self
    {
        $this->footerViewName = $view;

        $this->footerData = $data;

        return $this;
    }

    public function landscape(): self
    {
        return $this->orientation(Orientation::Landscape);
    }

    public function portrait(): self
    {
        return $this->orientation(Orientation::Portrait);
    }

    public function orientation(string|Orientation $orientation): self
    {
        if ($orientation instanceof Orientation) {
            $orientation = $orientation->value;
        }

        $this->orientation = $orientation;

        return $this;
    }

    public function inline(string $downloadName = ''): self
    {
        $this->name($downloadName);

        $this->addHeaders([
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="'.$this->downloadName.'"',
        ]);

        return $this;
    }

    public function html(string $html): self
    {
        $this->html = $html;

        return $this;
    }

    public function headerHtml(string $html): self
    {
        $this->headerHtml = $html;

        return $this;
    }

    public function footerHtml(string $html): self
    {
        $this->footerHtml = $html;

        return $this;
    }

    public function download(?string $downloadName = null): self
    {
        $this->downloadName ?: $this->name($downloadName ?? 'download');

        $this->addHeaders([
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="'.$this->downloadName.'"',
        ]);

        return $this;
    }

    public function headers(array $headers): self
    {
        $this->addHeaders($headers);

        return $this;
    }

    public function name(string $downloadName): self
    {
        if (! str_ends_with(strtolower($downloadName), '.pdf')) {
            $downloadName .= '.pdf';
        }

        $this->downloadName = $downloadName;

        return $this;
    }

    public function base64(): string
    {
        return $this
            ->getBrowsershot()
            ->base64pdf();
    }

    public function margins(
        float $top = 0,
        float $right = 0,
        float $bottom = 0,
        float $left = 0,
        Unit|string $unit = 'mm'
    ): self {
        if ($unit instanceof Unit) {
            $unit = $unit->value;
        }

        $this->margins = compact(
            'top',
            'right',
            'bottom',
            'left',
            'unit',
        );

        return $this;
    }

    public function format(string|Format $format): self
    {
        if ($format instanceof Format) {
            $format = $format->value;
        }

        $this->format = $format;

        return $this;
    }

    public function paperSize(float $width, float $height, Unit|string $unit = 'mm'): self
    {
        if ($unit instanceof Unit) {
            $unit = $unit->value;
        }

        $this->paperSize = compact(
            'width',
            'height',
            'unit',
        );

        return $this;
    }

    public function withBrowsershot(callable $callback): self
    {
        $this->customizeBrowsershot = $callback;

        return $this;
    }

    public function onLambda(): self
    {
        $this->onLambda = true;

        return $this;
    }

    public function save(string $path): self
    {
        if ($this->diskName) {
            return $this->saveOnDisk($this->diskName, $path);
        }

        $this
            ->getBrowsershot()
            ->save($path);

        return $this;
    }

    public function disk(string $diskName, string $visibility = 'private'): self
    {
        $this->diskName = $diskName;
        $this->visibility = $visibility;

        return $this;
    }

    protected function saveOnDisk(string $diskName, string $path): self
    {
        $pdfContent = $this->getBrowsershot()->pdf();
        $visibility = $this->visibility;

        Storage::disk($diskName)->put($path, $pdfContent, $visibility);

        return $this;
    }

    protected function getHtml(): string
    {
        if ($this->viewName) {
            $this->html = view($this->viewName, $this->viewData)->render();
        }

        if ($this->html) {
            return $this->html;
        }

        return '&nbsp';
    }

    protected function getHeaderHtml(): ?string
    {
        if ($this->headerViewName) {
            $this->headerHtml = view($this->headerViewName, $this->headerData)->render();
        }

        if ($this->headerHtml) {
            return $this->headerHtml;
        }

        return null;
    }

    protected function getFooterHtml(): ?string
    {
        if ($this->footerViewName) {
            $this->footerHtml = view($this->footerViewName, $this->footerData)->render();
        }

        if ($this->footerHtml) {
            return $this->footerHtml;
        }

        return null;
    }

    protected function getAllHtml(): string
    {
        return implode(PHP_EOL, [
            $this->getHeaderHtml(),
            $this->getHtml(),
            $this->getFooterHtml(),
        ]);
    }

    protected function getBrowsershot(): Browsershot
    {
        $browsershotClass = $this->onLambda
            ? BrowsershotLambda::class
            : Browsershot::class;

        $browsershot = $browsershotClass::html($this->getHtml());

        $browsershot->showBackground();

        $headerHtml = $this->getHeaderHtml();

        $footerHtml = $this->getFooterHtml();

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

        if ($this->margins) {
            $browsershot->margins(...$this->margins);
        }

        if ($this->format) {
            $browsershot->format($this->format);
        }

        if ($this->paperSize) {
            $browsershot->paperSize(...$this->paperSize);
        }

        if ($this->orientation === Orientation::Landscape->value) {
            $browsershot->landscape();
        }

        if ($this->customizeBrowsershot) {
            ($this->customizeBrowsershot)($browsershot);
        }

        return $browsershot;
    }

    public function toResponse($request): Response
    {
        if (! $this->hasHeader('Content-Disposition')) {
            $this->inline($this->downloadName);
        }

        $pdfContent = $this->getBrowsershot()->pdf();

        return response($pdfContent, 200, $this->responseHeaders);
    }

    protected function addHeaders(array $headers): self
    {
        $this->responseHeaders = array_merge($this->responseHeaders, $headers);

        return $this;
    }

    protected function hasHeader(string $headerName): bool
    {
        return array_key_exists($headerName, $this->responseHeaders);
    }

    public function isInline(): bool
    {
        if (! $this->hasHeader('Content-Disposition')) {
            return false;
        }

        return str_contains($this->responseHeaders['Content-Disposition'], 'inline');
    }

    public function isDownload(): bool
    {
        if (! $this->hasHeader('Content-Disposition')) {
            return false;
        }

        return str_contains($this->responseHeaders['Content-Disposition'], 'attachment');
    }

    public function contains(string|array $text): bool
    {
        if (is_string($text)) {
            $text = [$text];
        }

        $html = $this->getAllHtml();

        foreach ($text as $singleText) {
            if (str_contains($html, $singleText)) {
                return true;
            }
        }

        return false;
    }
}

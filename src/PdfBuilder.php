<?php

namespace Spatie\LaravelPdf;

use Closure;
use DateTimeInterface;
use Illuminate\Contracts\Support\Responsable;
use Illuminate\Foundation\Bus\PendingDispatch;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Traits\Conditionable;
use Illuminate\Support\Traits\Dumpable;
use Illuminate\Support\Traits\Macroable;
use Spatie\LaravelPdf\Drivers\PdfDriver;
use Spatie\LaravelPdf\Enums\Format;
use Spatie\LaravelPdf\Enums\Orientation;
use Spatie\LaravelPdf\Enums\Unit;
use Spatie\LaravelPdf\Exceptions\CouldNotGeneratePdf;
use Spatie\LaravelPdf\Jobs\GeneratePdfJob;
use Spatie\TemporaryDirectory\TemporaryDirectory;

class PdfBuilder implements Responsable
{
    use Conditionable;
    use Dumpable;
    use Macroable;

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

    public ?float $scale = null;

    public ?string $pageRanges = null;

    public bool $tagged = false;

    public ?PdfMetadata $metadata = null;

    protected string $visibility = 'private';

    protected ?Closure $customizeBrowsershot = null;

    protected array $responseHeaders = [
        'Content-Type' => 'application/pdf',
    ];

    protected bool $onLambda = false;

    protected ?string $diskName = null;

    protected ?PdfDriver $driver = null;

    protected ?string $driverName = null;

    public function setDriver(PdfDriver $driver): self
    {
        $this->driver = $driver;

        return $this;
    }

    public function driver(string $driverName): self
    {
        $this->driverName = $driverName;

        $this->driver = null;

        return $this;
    }

    protected function getDriver(): PdfDriver
    {
        if ($this->driver) {
            $driver = $this->driver;
        } elseif ($this->driverName) {
            $driver = app("laravel-pdf.driver.{$this->driverName}");
        } else {
            $driver = app(PdfDriver::class);
        }

        $this->configureBrowsershotDriver($driver);

        return $driver;
    }

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
        return base64_encode($this->generatePdfContent());
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

    public function scale(float $scale): self
    {
        $this->scale = $scale;

        return $this;
    }

    public function pageRanges(string $pageRanges): self
    {
        $this->pageRanges = $pageRanges;

        return $this;
    }

    public function tagged(): self
    {
        $this->tagged = true;

        return $this;
    }

    public function meta(
        ?string $title = null,
        ?string $author = null,
        ?string $subject = null,
        ?string $keywords = null,
        ?string $creator = null,
        string|DateTimeInterface|null $creationDate = null,
    ): self {
        if ($creationDate instanceof DateTimeInterface) {
            $offset = str_replace(':', "'", $creationDate->format('P'))."'";
            $creationDate = 'D:'.$creationDate->format('YmdHis').$offset;
        }

        $this->metadata = new PdfMetadata(
            title: $title,
            author: $author,
            subject: $subject,
            keywords: $keywords,
            creator: $creator,
            creationDate: $creationDate,
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

        if ($this->hasMetadata()) {
            file_put_contents($path, $this->generatePdfContent());
        } else {
            $this->getDriver()->savePdf(
                $this->getHtml(),
                $this->getHeaderHtml(),
                $this->getFooterHtml(),
                $this->buildOptions(),
                $path,
            );
        }

        return $this;
    }

    public function saveQueued(string $path, ?string $connection = null, ?string $queue = null): QueuedPdfResponse
    {
        if ($this->customizeBrowsershot) {
            throw CouldNotGeneratePdf::cannotQueueWithBrowsershotClosure();
        }

        $driverName = $this->driverName ?? config('laravel-pdf.driver', 'browsershot');

        $jobClass = config('laravel-pdf.job', GeneratePdfJob::class);

        $job = new $jobClass(
            html: $this->getHtml(),
            headerHtml: $this->getHeaderHtml(),
            footerHtml: $this->getFooterHtml(),
            options: $this->buildOptions(),
            path: $path,
            diskName: $this->diskName,
            visibility: $this->visibility,
            driverName: $driverName,
            metadata: $this->metadata,
        );

        if ($connection) {
            $job->onConnection($connection);
        }

        if ($queue) {
            $job->onQueue($queue);
        }

        $dispatch = new PendingDispatch($job);

        return new QueuedPdfResponse($dispatch, $job);
    }

    public function disk(string $diskName, string $visibility = 'private'): self
    {
        $this->diskName = $diskName;
        $this->visibility = $visibility;

        return $this;
    }

    protected function saveOnDisk(string $diskName, string $path): self
    {
        $fileName = pathinfo($path, PATHINFO_BASENAME);

        $temporaryDirectory = (new TemporaryDirectory)->create();

        $this->getDriver()->savePdf(
            $this->getHtml(),
            $this->getHeaderHtml(),
            $this->getFooterHtml(),
            $this->buildOptions(),
            $temporaryDirectory->path($fileName),
        );

        $content = file_get_contents($temporaryDirectory->path($fileName));

        $temporaryDirectory->delete();

        $content = $this->applyMetadata($content);

        Storage::disk($diskName)->put($path, $content, $this->visibility);

        return $this;
    }

    public function getHtml(): string
    {
        if ($this->viewName) {
            $this->html = view($this->viewName, $this->viewData)->render();
        }

        if ($this->html) {
            return $this->html;
        }

        return '&nbsp';
    }

    public function getHeaderHtml(): ?string
    {
        if ($this->headerViewName) {
            $this->headerHtml = view($this->headerViewName, $this->headerData)->render();
        }

        if ($this->headerHtml) {
            return $this->headerHtml;
        }

        return null;
    }

    public function getFooterHtml(): ?string
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

    protected function buildOptions(): PdfOptions
    {
        $options = new PdfOptions;

        $options->format = $this->format;
        $options->paperSize = $this->paperSize;
        $options->margins = $this->margins;
        $options->orientation = $this->orientation;
        $options->scale = $this->scale;
        $options->pageRanges = $this->pageRanges;
        $options->tagged = $this->tagged;

        return $options;
    }

    protected function generatePdfContent(): string
    {
        $content = $this->getDriver()->generatePdf(
            $this->getHtml(),
            $this->getHeaderHtml(),
            $this->getFooterHtml(),
            $this->buildOptions(),
        );

        return $this->applyMetadata($content);
    }

    protected function applyMetadata(string $pdfContent): string
    {
        if (! $this->hasMetadata()) {
            return $pdfContent;
        }

        return PdfMetadataWriter::write($pdfContent, $this->metadata);
    }

    protected function hasMetadata(): bool
    {
        return $this->metadata !== null && ! $this->metadata->isEmpty();
    }

    protected function configureBrowsershotDriver(PdfDriver $driver): void
    {
        if (! $driver instanceof Drivers\BrowsershotDriver) {
            return;
        }

        $driver->onLambda($this->onLambda);
        $driver->customizeBrowsershot($this->customizeBrowsershot);
    }

    public function toResponse($request): Response
    {
        if (! $this->hasHeader('Content-Disposition')) {
            $this->inline($this->downloadName);
        }

        $pdfContent = $this->generatePdfContent();

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

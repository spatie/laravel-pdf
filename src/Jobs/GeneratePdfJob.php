<?php

namespace Spatie\LaravelPdf\Jobs;

use Closure;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use Laravel\SerializableClosure\SerializableClosure;
use Spatie\LaravelPdf\Drivers\PdfDriver;
use Spatie\LaravelPdf\PdfMetadata;
use Spatie\LaravelPdf\PdfMetadataWriter;
use Spatie\LaravelPdf\PdfOptions;
use Spatie\TemporaryDirectory\TemporaryDirectory;
use Throwable;

class GeneratePdfJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /** @var SerializableClosure[] */
    protected array $thenCallbacks = [];

    /** @var SerializableClosure[] */
    protected array $catchCallbacks = [];

    public function __construct(
        public string $html,
        public ?string $headerHtml,
        public ?string $footerHtml,
        public PdfOptions $options,
        public string $path,
        public ?string $diskName = null,
        public string $visibility = 'private',
        public ?string $driverName = null,
        public ?PdfMetadata $metadata = null,
    ) {}

    public function then(Closure $callback): self
    {
        $this->thenCallbacks[] = new SerializableClosure($callback);

        return $this;
    }

    public function catch(Closure $callback): self
    {
        $this->catchCallbacks[] = new SerializableClosure($callback);

        return $this;
    }

    public function handle(): void
    {
        $driver = $this->resolveDriver();

        if ($this->diskName) {
            $this->saveOnDisk($driver);
        } elseif ($this->hasMetadata()) {
            $content = $driver->generatePdf(
                $this->html,
                $this->headerHtml,
                $this->footerHtml,
                $this->options,
            );

            file_put_contents($this->path, $this->applyMetadata($content));
        } else {
            $driver->savePdf(
                $this->html,
                $this->headerHtml,
                $this->footerHtml,
                $this->options,
                $this->path,
            );
        }

        foreach ($this->thenCallbacks as $callback) {
            ($callback->getClosure())($this->path, $this->diskName);
        }
    }

    public function failed(Throwable $exception): void
    {
        foreach ($this->catchCallbacks as $callback) {
            ($callback->getClosure())($exception);
        }
    }

    protected function resolveDriver(): PdfDriver
    {
        if ($this->driverName) {
            return app("laravel-pdf.driver.{$this->driverName}");
        }

        return app(PdfDriver::class);
    }

    protected function saveOnDisk(PdfDriver $driver): void
    {
        $fileName = pathinfo($this->path, PATHINFO_BASENAME);

        $temporaryDirectory = (new TemporaryDirectory)->create();

        $driver->savePdf(
            $this->html,
            $this->headerHtml,
            $this->footerHtml,
            $this->options,
            $temporaryDirectory->path($fileName),
        );

        $content = file_get_contents($temporaryDirectory->path($fileName));

        $temporaryDirectory->delete();

        $content = $this->applyMetadata($content);

        Storage::disk($this->diskName)->put($this->path, $content, $this->visibility);
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
}

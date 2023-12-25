<?php

namespace Spatie\LaravelPdf;

use Closure;
use Illuminate\Contracts\Support\Responsable;
use Spatie\Browsershot\Browsershot;
use Spatie\LaravelPdf\Enums\Orientation;
use Spatie\LaravelPdf\Enums\PaperFormat;

class Pdf implements Responsable
{
    public string $viewName = '';
    public array $data = [];
    public string $html = '';
    public string $downloadName = '';
    public bool $inline = false;
    public ?string $paperFormat = null;
    public ?string $orientation = null;

    public ?array $margins = null;

    protected ?Closure $customizeBrowsershot = null;

    public function view(string $view, array $data = []): self
    {
        $this->viewName = $view;

        $this->data = $data;

        return $this;
    }

    public function orientation(string|Orientation $orientation): self
    {
        if ($orientation instanceof Orientation) {
            $orientation = $orientation->value;
        }

        $this->orientation = $orientation;

        return $this;
    }

    public function inline(): self
    {
        $this->inline = true;

        return $this;
    }

    public function html(string $html): self
    {
        $this->html = $html;

        return $this;
    }

    public function download(string $downloadName): self
    {
        $this->name($downloadName);

        return $this;
    }

    public function name(string $downloadName): self
    {
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
        string $unit = 'mm')
    {
        $this->margins = compact(
            'top',
            'right',
            'bottom',
            'left',
            'unit',
        );

        return $this;
    }

    public function paperFormat(string|PaperFormat $paperFormat): self
    {
        if ($paperFormat instanceof PaperFormat) {
            $paperFormat = $paperFormat->value;
        }

        $this->paperFormat = $paperFormat;

        return $this;
    }

    public function withBrowsershot(callable $callback): self
    {
        $this->customizeBrowsershot = $callback;

        return $this;
    }

    public function save(string $path): self
    {
        $this
            ->getBrowsershot()
            ->save($path);

        return $this;
    }

    protected function getHtml(): string
    {
        if ($this->html) {
            return $this->html;
        }

        return view($this->viewName, $this->data)->render();
    }

    protected function getBrowsershot(): Browsershot
    {
        $browsershot = Browsershot::html($this->getHtml());

        if ($this->margins) {
            $browsershot->margins(... $this->margins);
        }

        if ($this->paperFormat) {
            $browsershot->format($this->paperFormat);
        }

        if ($this->orientation === Orientation::Landscape->value) {
            $browsershot->landscape();
        }

        if ($this->customizeBrowsershot) {
            ($this->customizeBrowsershot)($browsershot);
        }

        return $browsershot;
    }

    public function toResponse($request)
    {
        // TODO: Implement toResponse() method.
    }
}

/*
 * return pdf('my.view', [
   'title' => 'my title',
]);

return pdf('my.view', [
   'title' => 'my title',
])->inline();

return pdf('my.view', [
   'title' => 'my title',
])->download($downloadName);


Pdf::view('my.view', $data
   ->margins(10,20,30,40)
   ->orientation(Orientation::Landscape)
   ->paper(Paper::A3) // da's eigenlijk een A5 dan
   ->save($path);

Pdf::assertSee($text);
Pdf::assertViewIs($viewName);
 */

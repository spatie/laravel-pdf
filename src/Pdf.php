<?php

namespace Spatie\LaravelPdf;

use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Contracts\Support\Responsable;
use Illuminate\Support\Facades\Blade;
use Spatie\LaravelPdf\Facades\Pdf as Builder;
use Illuminate\View\Component;
use Illuminate\View\View;
use Spatie\LaravelPdf\Enums\Format;
use Spatie\LaravelPdf\Enums\Orientation;

abstract class Pdf extends Component implements Responsable, Htmlable
{
    protected ?Builder $builder = null;

    public function getPdfBuilder(): PdfBuilder
    {
        return $this->builder ??= Builder::html($this->toHtml())
            ->when($this->name(), fn (Builder $builder, string $name) => $builder->name($name))
            ->when($this->header(), fn (Builder $builder, View | Htmlable | string $header) => $builder->headerHtml(match (true) {
                $header instanceof View => $header->render(),
                $header instanceof Htmlable => $header->toHtml(),
                default => $header,
            }))
            ->when($this->footer(), fn (Builder $builder, View | Htmlable | string $footer) => $builder->footerHtml(match (true) {
                $footer instanceof View => $footer->render(),
                $footer instanceof Htmlable => $footer->toHtml(),
                default => $footer,
            }))
            ->when($this->format(), fn (Builder $builder, Format | string $format) => $builder->format($format))
            ->when($this->disk(), fn (Builder $builder, string $disk) => $builder->disk($disk))
            ->orientation($this->orientation());
    }

    public function header(): View | Htmlable | string | null
    {
        return null;
    }

    public function footer(): View | Htmlable | string | null
    {
        return null;
    }

    public function orientation(): Orientation | string
    {
        return Orientation::Portrait;
    }

    public function format(): Format | string | null
    {
        return null;
    }

    public function name(): ?string
    {
        return null;
    }

    public function download(?string $downloadName = null): static
    {
        $this->getPdfBuilder()->download($downloadName ?? $this->name());

        return $this;
    }

    public function inline(?string $downloadName = null): static
    {
        $this->getPdfBuilder()->inline($downloadName ?? $this->name());

        return $this;
    }

    public function save(string $path): static
    {
        $this->getPdfBuilder()->save($path);

        return $this;
    }

    public function disk(): ?string
    {
        return null;
    }

    public function base64(): string
    {
        return $this->getPdfBuilder()->base64();
    }

    public function toHtml()
    {
        return Blade::renderComponent($this);
    }

    public function toResponse($request)
    {
        return $this->getPdfBuilder()->toResponse($request);
    }
}

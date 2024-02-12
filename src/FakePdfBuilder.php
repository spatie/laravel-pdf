<?php

namespace Spatie\LaravelPdf;

use Closure;
use Illuminate\Http\Response;
use PHPUnit\Framework\Assert;

class FakePdfBuilder extends PdfBuilder
{
    /** @var array<int, PdfBuilder> */
    protected array $respondedWithPdf = [];

    /** @var array<int, PdfBuilder> */
    protected array $savedPdfs = [];

    public function save(string $path): self
    {
        $this->getBrowsershot();

        $this->savedPdfs[] = [
            'pdf' => clone $this,
            'path' => $path,
        ];

        return $this;
    }

    public function toResponse($request): Response
    {
        $this->respondedWithPdf[] = clone $this;

        return new Response();
    }

    public function assertViewIs(string $viewName): void
    {
        foreach ($this->savedPdfs as $savedPdf) {
            if ($savedPdf['pdf']->viewName === $viewName) {
                $this->markAssertionPassed();

                return;
            }
        }

        Assert::fail("Did not save a PDF that uses view `{$viewName}`");
    }

    public function assertViewHas(string $key, $value = null): void
    {
        if ($value === null) {
            foreach ($this->savedPdfs as $savedPdf) {
                if (array_key_exists($key, $savedPdf['pdf']->viewData)) {
                    $this->markAssertionPassed();

                    return;
                }
            }

            Assert::fail("Did not save a PDF that has view data `{$key}`");
        }

        foreach ($this->savedPdfs as $savedPdf) {
            if (! array_key_exists($key, $savedPdf['pdf']->viewData)) {
                continue;
            }

            if ($savedPdf['pdf']->viewData[$key] === $value) {
                $this->markAssertionPassed();

                return;
            }
        }

        Assert::fail("Did not save a PDF that has view data `{$key}` with value `{$value}`");
    }

    public function assertSaved(string|callable $path): void
    {
        if (is_string($path)) {
            foreach ($this->savedPdfs as $savedPdf) {
                if ($savedPdf['path'] === $path) {
                    $this->markAssertionPassed();

                    return;
                }
            }

            Assert::fail("Did not save a PDF to `{$path}`");
        }

        $callable = $path;
        foreach ($this->savedPdfs as $savedPdf) {
            $result = $callable($savedPdf['pdf'], $savedPdf['path']);

            if ($result === true) {
                $this->markAssertionPassed();

                return;
            }
        }

        Assert::fail('Did not save a PDF that matched the expectations');
    }

    public function assertSee(string|array $text): void
    {
        if (is_string($text)) {
            $text = [$text];
        }

        foreach ($this->savedPdfs as $savedPdf) {
            foreach ($text as $singleText) {
                if (! str_contains($savedPdf['pdf']->html, $singleText)) {
                    break 2; // jump out of the inner foreach loop
                }
            }

            $this->markAssertionPassed();

            return;
        }

        $texts = collect($text)
            ->wrap('`')
            ->join(',', ', and ');

        Assert::fail("Did not save a PDF that contains {$texts}");
    }

    public function assertRespondedWithPdf(Closure $expectations): void
    {
        Assert::assertNotEmpty($this->respondedWithPdf);

        foreach ($this->respondedWithPdf as $pdf) {
            $result = $expectations($pdf);

            if ($result === true) {
                $this->markAssertionPassed();

                return;
            }
        }

        Assert::fail('Did not respond with a PDF that matched the expectations');
    }

    protected function markAssertionPassed(): void
    {
        Assert::assertTrue(true);
    }
}

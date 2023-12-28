<?php

namespace Spatie\LaravelPdf;

use Closure;
use Illuminate\Http\Response;
use PHPUnit\Framework\Assert;

class FakePdf extends Pdf
{
    /** @var array<int, \Spatie\LaravelPdf\Pdf> */
    protected array $respondedWithPdf = [];

    /** @var array<int, \Spatie\LaravelPdf\Pdf> */
    protected array $savedPdfs = [];

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

    public function toResponse($request): Response
    {
        $this->respondedWithPdf[] = $this;

        return new Response();
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

    public function save(string $path): self
    {
        $this->getBrowsershot();

        $this->savedPdfs[] = [
            'pdf' => $this,
            'path' => $path,
        ];

        return $this;
    }

    protected function markAssertionPassed(): void
    {
        Assert::assertTrue(true);
    }
}

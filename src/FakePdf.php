<?php

namespace Spatie\LaravelPdf;

use Closure;
use Illuminate\Http\Response;
use PHPUnit\Framework\Assert;

class FakePdf extends Pdf
{
    /** @var array<int, \Spatie\LaravelPdf\Pdf> */
    protected array $respondedWithPdf = [];

    public function assertViewIs(string $viewName): self
    {
        Assert::assertEquals($viewName, $this->viewName);

        return $this;
    }

    public function toResponse($request): Response
    {
        $this->respondedWithPdf[] = $this;

        return new Response();
    }

    public function assertRespondedWithPdf(Closure $expectations): void
    {
        Assert::assertNotEmpty($this->respondedWithPdf);

        foreach($this->respondedWithPdf as $pdf) {

            $result = $expectations($pdf);

            if ($result === true) {
                return;
            }

        }

        Assert::fail('Did not respond with a PDF that matched the expectations');
    }
}

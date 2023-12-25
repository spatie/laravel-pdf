<?php

namespace Spatie\LaravelPdf;

use PHPUnit\Framework\Assert;

class FakePdf extends Pdf
{
    public function assertViewIs(string $viewName): self
    {
        Assert::assertEquals($viewName, $this->viewName);

        return $this;
    }
}

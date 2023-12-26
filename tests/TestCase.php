<?php

namespace Spatie\LaravelPdf\Tests;

use Orchestra\Testbench\TestCase as Orchestra;
use Spatie\LaravelPdf\PdfServiceProvider;

class TestCase extends Orchestra
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->app->view->addLocation(__DIR__.'/TestSupport/Views');
    }

    protected function getPackageProviders($app)
    {
        return [
            PdfServiceProvider::class,
        ];
    }
}

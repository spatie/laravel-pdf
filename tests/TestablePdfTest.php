<?php

use Spatie\LaravelPdf\Pdf;

it('can be used to test Pdf classes', function () {
    ExamplePdf::test()
        ->assertSee('Hello, Spatie!');
});

class ExamplePdf extends Pdf
{
    public function __construct(
        public string $user = 'Spatie',
    ) {}

    public function render()
    {
        return <<<'BLADE'
            Hello, {{ $user }}!
        BLADE;
    }
}

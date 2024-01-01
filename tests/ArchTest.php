<?php

use Illuminate\Support\Facades\Facade;

arch('will not use debugging functions')
    ->expect(['dd', 'dump', 'ray'])
    ->each->not->toBeUsed();

arch('Spatie\LaravelPdf\Enums namespace only contains enums')
    ->expect('Spatie\LaravelPdf\Enums')
    ->toBeEnums();

arch('Spatie\LaravelPdf\Facades namespace only contains facades')
    ->expect('Spatie\LaravelPdf\Facades')
    ->toExtend(Facade::class);

<?php

use Spatie\Browsershot\Browsershot;
use Spatie\LaravelPdf\Enums\Orientation;
use Spatie\LaravelPdf\Enums\PaperFormat;
use Spatie\LaravelPdf\Facades\Pdf;

use function Spatie\LaravelPdf\Support\pdf;

beforeEach(function () {
    $this->targetPath = getTempPath('test.pdf');
});

it('can create a pdf using the function', function () {
    pdf('test')->save($this->targetPath);

    expect($this->targetPath)->toContainText('This is a test');
});

it('can accept margins', function () {
    Pdf::view('test')->margins(200)->save($this->targetPath);

    assertMatchesPdfSnapshot($this->targetPath);
})->skipOnLinux();

it('can accept html', function () {
    Pdf::html('<h1>Some custom HTML</h1>')->save($this->targetPath);

    expect($this->targetPath)->toContainText('Some custom HTML');
});

it('can accept header html', function () {
    Pdf::headerHtml('Header html')
        ->html('Body html')
        ->save($this->targetPath);

    expect($this->targetPath)->toContainText([
        'Header html',
        'Body html',
    ]);
});

it('can accept footer html', function () {
    Pdf::html('Body html')
        ->footerHtml('Footer html')
        ->save($this->targetPath);

    expect($this->targetPath)->toContainText([
        'Body html',
        'Footer html',
    ]);
});

it('can render header html', function () {
    Pdf::html('Body html')
        ->headerView('header', ['title' => 'Header title'])
        ->save($this->targetPath);

    expect($this->targetPath)->toContainText([
        'This is the header HTML: Header title',
        'Body html',
    ]);
});

it('can render footer html', function () {
    Pdf::html('Body html')
        ->footerView('footer', ['title' => 'Footer title'])
        ->save($this->targetPath);

    expect($this->targetPath)->toContainText([
        'This is the footer HTML: Footer title',
        'Body html',
    ]);
});

it('can create a pdf using the facade', function () {
    Pdf::view('test')->save($this->targetPath);

    expect($this->targetPath)->toContainText('This is a test');
});

it('can return the base 64 encoded pdf', function () {
    $base64string = Pdf::view('test')->base64();

    expect($base64string)->toBeString();
});

it('can accept the paper format', function () {
    Pdf::view('test')
        ->paperFormat(PaperFormat::A5)
        ->save($this->targetPath);

    expect($this->targetPath)
        ->toHaveDimensions(419, 595)
        ->toContainText('This is a test');
})->skipOnLinux();

it('can accept the orientation', function () {
    Pdf::view('test')
        ->orientation(Orientation::Landscape)
        ->save($this->targetPath);

    expect($this->targetPath)
        ->toHaveDimensions(792, 612)
        ->toContainText('This is a test');
});

it('can customize browsershot', function () {
    Pdf::view('test')
        ->withBrowsershot(function (Browsershot $browsershot) {
            $browsershot->landscape();
        })
        ->save($this->targetPath);

    expect($this->targetPath)
        ->toHaveDimensions(792, 612)
        ->toContainText('This is a test');
});

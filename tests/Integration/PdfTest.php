<?php

use Illuminate\Support\Facades\Storage;
use Spatie\Browsershot\Browsershot;
use Spatie\LaravelPdf\Enums\Format;
use Spatie\LaravelPdf\Enums\Orientation;
use Spatie\LaravelPdf\Facades\Pdf;

use function Spatie\LaravelPdf\Support\pdf;

beforeEach(function () {
    $this->targetPath = getTempPath('test.pdf');
});

it('can create a pdf using the function', function () {
    pdf('test')->save($this->targetPath);

    expect($this->targetPath)->toContainText('This is a test');
});

it('can save a pdf to a disk', function () {
    Storage::fake('local');

    Pdf::view('test')
        ->disk('local')
        ->save('test.pdf');

    Storage::disk('local')->assertExists('test.pdf');
});

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
})->skipOnGitHubActions();

it('can render footer html', function () {
    Pdf::html('Body html')
        ->footerView('footer', ['title' => 'Footer title'])
        ->save($this->targetPath);

    expect($this->targetPath)->toContainText([
        'This is the footer HTML: Footer title',
        'Body html',
    ]);
})->skipOnGitHubActions();

it('can create a pdf using the facade', function () {
    Pdf::view('test')->save($this->targetPath);

    expect($this->targetPath)->toContainText('This is a test');
});

it('can create an empty pdf', function () {
    Pdf::html('')->save($this->targetPath);

    expect($this->targetPath)->toBeFile();
});

it('can return the base 64 encoded pdf', function () {
    $base64string = Pdf::view('test')->base64();

    expect($base64string)->toBeString();
});

it('can accept the paper format', function () {
    Pdf::view('test')
        ->format(Format::A5)
        ->save($this->targetPath);

    expect($this->targetPath)
        ->toHaveDimensions(419, 595)
        ->toContainText('This is a test');
});

it('can accept the page size', function () {
    Pdf::view('test')
        ->paperSize(200, 400, 'mm')
        ->save($this->targetPath);

    expect($this->targetPath)
        ->toHaveDimensions(567, 1134)
        ->toContainText('This is a test');
});

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

it('will use a fresh instance after saving', function () {
    Pdf::view('test')->landscape()->save(getTempPath('first.pdf'));

    Pdf::view('test')->save(getTempPath('second.pdf'));

    // first pdf is landscape
    expect(getTempPath('first.pdf'))
        ->toHaveDimensions(792, 612);

    // second pdf is portrait
    expect(getTempPath('second.pdf'))
        ->toHaveDimensions(612, 792);
});

it('will execute javascript', function () {
    Pdf::view('javascript')->save($this->targetPath);

    expect($this->targetPath)->toContainText('hello');
});

it('can save as png in local and disk', function () {
    Storage::fake('local');

    $firstPath = getTempPath('first.png');
    Pdf::view('test')
        ->save($firstPath);

    expect(mime_content_type($firstPath))
        ->toBe('image/png');

    Pdf::view('test')
        ->disk('local')
        ->save('second.png');

    expect(Storage::disk('local')
        ->mimeType('second.png'))
        ->toBe('image/png');
});

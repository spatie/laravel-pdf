<?php

use Spatie\LaravelPdf\Facades\Pdf;

beforeEach(function () {
    $this->targetPath = getTempPath('test.pdf');
});

it('can set a page break', function () {
    Pdf::view('blade-directives.body')
        ->save($this->targetPath);

    expect($this->targetPath)->toHavePageCount(2);
});

it('can display the number of pages', function () {
    Pdf::view('blade-directives.body')
        ->footerView('blade-directives.footer')
        ->save($this->targetPath);

    expect($this->targetPath)->toContainText('page 1 of 2');
});

it('can display an image using a static string with an absolute path', function () {
    Pdf::view('blade-directives.body')
        ->headerView('blade-directives.image-header-using-a-static-absolute-path')
        ->save($this->targetPath);

    expect(true)->toBeTrue();
});

it('can display an image using a static string with an relative path', function () {
    Pdf::view('blade-directives.body')
        ->headerView('blade-directives.image-header-using-a-static-relative-path')
        ->save($this->targetPath);

    expect(true)->toBeTrue();
});

it('can display an image using a variable with an absolute path', function () {
    Pdf::view('blade-directives.body')
        ->headerView('blade-directives.image-header-using-a-variable', [
            'logo' => 'https://avatars.githubusercontent.com/u/7535935?s=200&v=4',
        ])
        ->save($this->targetPath);

    expect(true)->toBeTrue();
});

it('can display an image using a variable with an relative path', function () {

    $logoPath = \Orchestra\Testbench\workbench_path('public/assets/logo.png');

    Pdf::view('blade-directives.body')
        ->headerView('blade-directives.image-header-using-a-variable', [
            'logo' => "../../../../../../../../../$logoPath",
        ])
        ->save($this->targetPath);

    expect(true)->toBeTrue();
});

it('can detect inlined image type', function () {
    $html = view('blade-directives.image-header-using-a-variable', [
        'logo' => \Orchestra\Testbench\workbench_path('public/assets/logo.svg'),
    ])->render();

    expect(str_contains($html, 'data:image/svg+xml;base64,'))->toBeTrue();
});

it('can throw view exception with image relative path', function () {

    Pdf::view('blade-directives.body')
        ->headerView('blade-directives.image-header-using-a-variable', [
            'logo' => './not-found.png',
        ])
        ->save($this->targetPath);
})->throws(\Illuminate\View\ViewException::class, 'Image not found:');

it('can throw view exception with image absolute path', function () {

    Pdf::view('blade-directives.body')
        ->headerView('blade-directives.image-header-using-a-variable', [
            'logo' => 'https://picsum.photos/not-found',
        ])
        ->save($this->targetPath);
})->throws(\Illuminate\View\ViewException::class, 'Failed to fetch the image:');

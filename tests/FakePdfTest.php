<?php

use Illuminate\Support\Facades\Route;
use Spatie\LaravelPdf\Facades\Pdf;
use Spatie\LaravelPdf\PdfBuilder;

use function Spatie\LaravelPdf\Support\pdf;

beforeEach(function () {
    Pdf::fake();
});

it('can determine the view that was used', function () {
    Pdf::view('test')->save('my-custom-name.pdf');

    Pdf::assertViewIs('test');
});

it('can determine the view that was not used', function () {
    Pdf::view('test')->save('my-custom-name.pdf');

    Pdf::assertViewIs('this-view-does-not-exist');
})->fails();

it('can determine that a certain piece of data was passed to the view', function () {
    Pdf::view('test', ['foo' => 'bar'])->save('my-custom-name.pdf');

    Pdf::assertViewHas('foo');
});

it('can determine that a certain piece of data was not passed to the view', function () {
    Pdf::view('test', ['foo' => 'bar'])->save('my-custom-name.pdf');

    Pdf::assertViewHas('key-does-not-exist');
})->fails();

it('can determine that a certain piece of data was passed to the view with a certain value', function () {
    Pdf::view('test', ['foo' => 'bar'])->save('my-custom-name.pdf');

    Pdf::assertViewHas('foo', 'bar');
});

it('can determine that a certain piece of data was not passed to the view with a certain value', function () {
    Pdf::view('test', ['foo' => 'bar'])->save('my-custom-name.pdf');

    Pdf::assertViewHas('foo', 'this-value-does-not-exist');
})->fails();

it('can determine that the pdf content contains a certain string', function () {
    Pdf::view('test')->save('my-custom-name.pdf');

    Pdf::assertSee('test');
});

it('can determine that the pdf content does not contain a certain string', function () {
    Pdf::view('test')->save('my-custom-name.pdf');

    Pdf::assertSee('this-string-does-not-exist');
})->fails();

it('can determine that the pdf content contains multiple strings', function () {
    Pdf::view('test')->save('my-custom-name.pdf');

    Pdf::assertSee([
        'This',
        'test',
    ]);
});

it('can determine that the pdf content does not contain multiple strings', function () {
    Pdf::view('test')->save('my-custom-name.pdf');

    Pdf::assertSee([
        'This',
        'this-string-is-not-present-in-the-pdf',
        'test',
    ]);
})->fails();

it('can determine that the pdf content does not contain a unexpected string', function () {
    Pdf::view('test')->save('my-custom-name.pdf');

    Pdf::assertDontSee('this-string-does-not-exist');
});

it('can determine that the pdf content contains a expected string', function () {
    Pdf::view('test')->save('my-custom-name.pdf');

    Pdf::assertDontSee('test');
})->fails();

it('can determine that the pdf content does not contain multiple unexpected strings', function () {
    Pdf::view('test')->save('my-custom-name.pdf');

    Pdf::assertDontSee([
        'this-string-is-not-present-in-the-pdf',
        'this-string-is-not-present-in-the-pdf-as-well',
        'this-string-is-not-present-in-the-pdf-either',
    ]);
});

it('can determine that the pdf content contains multiple expected strings', function () {
    Pdf::view('test')->save('my-custom-name.pdf');

    Pdf::assertDontSee([
        'This',
        'test',
    ]);
})->fails();

it('can determine that the pdf content contain an unexpected string between expected strings', function () {
    Pdf::view('test')->save('my-custom-name.pdf');

    Pdf::assertDontSee([
        'this',
        'this-string-is-not-present-in-the-pdf',
        'test',
    ]);
})->fails();

it('can determine that a pdf was saved a a certain path', function () {
    Pdf::view('test')->save('my-custom-name.pdf');

    Pdf::assertSaved('my-custom-name.pdf');
});

it('can determine that a pdf was not saved a a certain path', function () {
    Pdf::view('test')->save('my-custom-name.pdf');

    Pdf::assertSaved('non-existing.pdf');
})->fails();

it('can determine that a pdf was saved with certain properties', function () {
    Pdf::view('test')->save('my-custom-name.pdf');

    Pdf::view('another-test')->save('my-other-custom-name.pdf');

    Pdf::assertSaved(function (PdfBuilder $pdf) {
        return $pdf->viewName === 'test';
    });
});

it('can determine properties of the pdf that was returned in a response', function () {
    Route::get('pdf', function () {
        return pdf('test')->inline();
    });

    $this->get('pdf')->assertSuccessful();

    Pdf::assertRespondedWithPdf(function (PdfBuilder $pdf) {
        return $pdf->viewName === 'test'
            && $pdf->isInline();
    });
});

it('can determine that a PDF contained a certain piece of text', function () {
    Route::get('pdf', function () {
        return pdf('test')->inline();
    });

    $this->get('pdf')->assertSuccessful();

    Pdf::assertRespondedWithPdf(function (PdfBuilder $pdf) {
        return $pdf
            ->contains('test');
    });
});

it('can determine that a PDF did not contain a certain piece of text', function () {
    Route::get('pdf', function () {
        return pdf('test')->inline();
    });

    $this->get('pdf')->assertSuccessful();

    Pdf::assertRespondedWithPdf(function (PdfBuilder $pdf) {
        return $pdf->contains('this string does not exist in the PDF');
    });
})->fails();

it('can determine that a pdf did not have certain properties in a response', function () {
    Route::get('pdf', function () {
        return pdf('test')->inline();
    });

    $this->get('pdf')->assertSuccessful();

    Pdf::assertRespondedWithPdf(function (PdfBuilder $pdf) {
        return $pdf->isDownload();
    });
})->fails();

it('can determine if a pdf was saved with certain properties', function () {
    Pdf::view('test', ['foo' => 'bar'])->save('hey.pdf');

    Pdf::assertSaved(function (PdfBuilder $pdf) {
        return $pdf->viewName === 'test';
    });
});

it('can determine if a pdf was not saved with certain properties', function () {
    Pdf::view('test', ['foo' => 'bar'])->save('hey.pdf');

    Pdf::assertSaved(function (PdfBuilder $pdf) {
        return $pdf->viewName === 'non-existing-view';
    });
})->fails();

it('it will not combine properties of different instances', function () {
    Pdf::view('test', ['foo' => 'bar'])->save('first.pdf');

    Pdf::view('another-test')->save('second.pdf');

    Pdf::assertSaved(function (PdfBuilder $pdf) {
        return $pdf->viewName === 'another-test'
            && array_key_exists('foo', $pdf->viewData);
    });
})->fails();

it('can verify that a pdf was saved a given path', function () {
    Pdf::view('test')->save('my-name.pdf');

    Pdf::assertSaved(function (PdfBuilder $pdf, string $path) {
        return $path === 'my-name.pdf';
    });
});

it('can verify that a pdf was not saved a given path', function () {
    Pdf::view('test')->save('my-name.pdf');

    Pdf::assertSaved(function (PdfBuilder $pdf, string $path) {
        return $path === 'non-existing-path';
    });
})->fails();

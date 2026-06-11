---
title: Waiting for readiness
weight: 5
---

When you render JavaScript heavy views (charts, maps, web fonts, or content loaded asynchronously), the PDF can be captured before everything has finished rendering. The result is an empty chart or a missing font.

Instead of guessing with arbitrary delays, you can wait for an explicit readiness signal. Set a flag in your view once everything is done, and the PDF will only be captured after that flag becomes true.

## Signalling readiness from your view

In your Blade view, set `window.pdfReady` to `true` once the page is ready to be captured.

```blade
<canvas id="chart"></canvas>

<script>
    renderChart('#chart').then(() => {
        window.pdfReady = true;
    });
</script>
```

## Waiting for the flag

Call `waitUntilReady()` on the builder. The PDF will be captured only after `window.pdfReady === true`.

```php
use Spatie\LaravelPdf\Facades\Pdf;

Pdf::view('pdf.report', ['report' => $report])
    ->waitUntilReady()
    ->save('report.pdf');
```

## Using a custom expression

If you prefer your own readiness condition, pass any JavaScript expression that evaluates to a truthy value.

```php
Pdf::view('pdf.report')
    ->waitUntilReady('window.charts.every(chart => chart.rendered)')
    ->save('report.pdf');
```

## Setting a timeout

By default the renderer waits up to 30 seconds. You can pass a custom timeout (in milliseconds) as the second argument.

```php
Pdf::view('pdf.report')
    ->waitUntilReady('window.pdfReady === true', timeout: 5000)
    ->save('report.pdf');
```

## Driver support

Waiting for readiness requires a driver that can execute JavaScript. It is supported by the [Browsershot](/docs/laravel-pdf/v2/drivers/customizing-browsershot), [Chrome](/docs/laravel-pdf/v2/drivers/using-the-chrome-driver), and [Gotenberg](/docs/laravel-pdf/v2/drivers/using-the-gotenberg-driver) drivers.

When using Gotenberg, the readiness expression is passed as its `waitForExpression` field, and the timeout is applied to the HTTP request so it does not abort before Gotenberg finishes rendering.

Calling `waitUntilReady()` while a driver that cannot run JavaScript is active (such as DOMPDF or WeasyPrint) throws a `CouldNotGeneratePdf` exception.

<?php

namespace Spatie\LaravelPdf\Drivers;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use Spatie\LaravelPdf\Enums\Orientation;
use Spatie\LaravelPdf\Exceptions\CouldNotGeneratePdf;
use Spatie\LaravelPdf\PdfOptions;

class GotenbergDriver implements PdfDriver
{
    protected string $url;

    protected ?string $username;

    protected ?string $password;

    protected array $formatDimensions = [
        'letter' => ['width' => 8.5, 'height' => 11],
        'legal' => ['width' => 8.5, 'height' => 14],
        'tabloid' => ['width' => 11, 'height' => 17],
        'ledger' => ['width' => 17, 'height' => 11],
        'a0' => ['width' => 33.1, 'height' => 46.8],
        'a1' => ['width' => 23.39, 'height' => 33.1],
        'a2' => ['width' => 16.54, 'height' => 23.39],
        'a3' => ['width' => 11.69, 'height' => 16.54],
        'a4' => ['width' => 8.27, 'height' => 11.69],
        'a5' => ['width' => 5.83, 'height' => 8.27],
        'a6' => ['width' => 4.13, 'height' => 5.83],
    ];

    public function __construct(array $config = [])
    {
        $this->url = rtrim($config['url'] ?? 'http://localhost:3000', '/');
        $this->username = $config['username'] ?? null;
        $this->password = $config['password'] ?? null;
    }

    public function generatePdf(string $html, ?string $headerHtml, ?string $footerHtml, PdfOptions $options): string
    {
        $response = $this->buildRequest($html, $headerHtml, $footerHtml, $options)
            ->post($this->endpoint());

        if (! $response->successful()) {
            throw CouldNotGeneratePdf::gotenbergApiError($response->body());
        }

        return $response->body();
    }

    public function savePdf(string $html, ?string $headerHtml, ?string $footerHtml, PdfOptions $options, string $path): void
    {
        $pdfContent = $this->generatePdf($html, $headerHtml, $footerHtml, $options);

        file_put_contents($path, $pdfContent);
    }

    protected function endpoint(): string
    {
        return "{$this->url}/forms/chromium/convert/html";
    }

    protected function buildRequest(string $html, ?string $headerHtml, ?string $footerHtml, PdfOptions $options): PendingRequest
    {
        $request = Http::attach('files', $html, 'index.html')
            ->when(
                $this->username && $this->password,
                fn (PendingRequest $request): PendingRequest => $request->withBasicAuth(
                    $this->username,
                    $this->password
                )
            );

        if ($headerHtml) {
            $request = $request->attach('files', $headerHtml, 'header.html');
        }

        if ($footerHtml) {
            $request = $request->attach('files', $footerHtml, 'footer.html');
        }

        $formFields = $this->buildFormFields($options);

        foreach ($formFields as $key => $value) {
            $request = $request->attach($key, (string) $value);
        }

        return $request;
    }

    protected function buildFormFields(PdfOptions $options): array
    {
        $fields = [
            'printBackground' => 'true',
        ];

        if ($options->format) {
            $dimensions = $this->formatDimensions[strtolower($options->format)] ?? null;

            if ($dimensions) {
                $fields['paperWidth'] = $dimensions['width'].'in';
                $fields['paperHeight'] = $dimensions['height'].'in';
            }
        }

        if ($options->paperSize) {
            $unit = $options->paperSize['unit'] ?? 'mm';
            $fields['paperWidth'] = $options->paperSize['width'].$unit;
            $fields['paperHeight'] = $options->paperSize['height'].$unit;
        }

        if ($options->margins) {
            $unit = $options->margins['unit'] ?? 'mm';
            $fields['marginTop'] = $options->margins['top'].$unit;
            $fields['marginRight'] = $options->margins['right'].$unit;
            $fields['marginBottom'] = $options->margins['bottom'].$unit;
            $fields['marginLeft'] = $options->margins['left'].$unit;
        }

        if ($options->orientation === Orientation::Landscape->value) {
            $fields['landscape'] = 'true';
        }

        if ($options->scale !== null) {
            $fields['scale'] = (string) $options->scale;
        }

        if ($options->pageRanges !== null) {
            $fields['nativePageRanges'] = $options->pageRanges;
        }

        if ($options->tagged) {
            $fields['generateTaggedPdf'] = 'true';
        }

        return $fields;
    }
}

<?php

namespace Spatie\LaravelPdf\Drivers;

use Illuminate\Support\Facades\Http;
use Spatie\LaravelPdf\Enums\Orientation;
use Spatie\LaravelPdf\Exceptions\CouldNotGeneratePdf;
use Spatie\LaravelPdf\PdfOptions;

class CloudflareDriver implements PdfDriver
{
    protected string $apiToken;

    protected string $accountId;

    public function __construct(array $config = [])
    {
        $this->apiToken = $config['api_token'] ?? '';
        $this->accountId = $config['account_id'] ?? '';

        if (empty($this->apiToken) || empty($this->accountId)) {
            throw CouldNotGeneratePdf::missingCloudflareCredentials();
        }
    }

    public function generatePdf(string $html, ?string $headerHtml, ?string $footerHtml, PdfOptions $options): string
    {
        $requestBody = $this->buildRequestBody($html, $headerHtml, $footerHtml, $options);

        $response = Http::withToken($this->apiToken)
            ->post($this->endpoint(), $requestBody);

        if (! $response->successful()) {
            throw CouldNotGeneratePdf::cloudflareApiError($response->body());
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
        return "https://api.cloudflare.com/client/v4/accounts/{$this->accountId}/browser-rendering/pdf";
    }

    protected function buildRequestBody(string $html, ?string $headerHtml, ?string $footerHtml, PdfOptions $options): array
    {
        $pdfOptions = [
            'printBackground' => true,
        ];

        if ($options->format) {
            $pdfOptions['format'] = strtolower($options->format);
        }

        if ($options->paperSize) {
            $unit = $options->paperSize['unit'] ?? 'mm';
            $pdfOptions['width'] = $options->paperSize['width'].$unit;
            $pdfOptions['height'] = $options->paperSize['height'].$unit;
        }

        if ($options->margins) {
            $unit = $options->margins['unit'] ?? 'mm';
            $pdfOptions['margin'] = [
                'top' => $options->margins['top'].$unit,
                'right' => $options->margins['right'].$unit,
                'bottom' => $options->margins['bottom'].$unit,
                'left' => $options->margins['left'].$unit,
            ];
        }

        if ($options->orientation === Orientation::Landscape->value) {
            $pdfOptions['landscape'] = true;
        }

        if ($options->scale !== null) {
            $pdfOptions['scale'] = $options->scale;
        }

        if ($options->pageRanges !== null) {
            $pdfOptions['pageRanges'] = $options->pageRanges;
        }

        if ($options->tagged) {
            $pdfOptions['tagged'] = true;
        }

        if ($headerHtml || $footerHtml) {
            $pdfOptions['displayHeaderFooter'] = true;

            if ($headerHtml) {
                $pdfOptions['headerTemplate'] = $headerHtml;
            }

            if ($footerHtml) {
                $pdfOptions['footerTemplate'] = $footerHtml;
            }
        }

        return [
            'html' => $html,
            'pdfOptions' => $pdfOptions,
        ];
    }
}

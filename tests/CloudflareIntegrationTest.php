<?php

use Dotenv\Dotenv;
use Spatie\LaravelPdf\Drivers\CloudflareDriver;
use Spatie\LaravelPdf\PdfOptions;

beforeEach(function () {
    if (file_exists(__DIR__.'/../.env')) {
        $dotenv = Dotenv::createImmutable(__DIR__.'/..');
        $dotenv->safeLoad();
    }

    $this->apiToken = env('CLOUDFLARE_API_TOKEN');

    $this->accountId = env('CLOUDFLARE_ACCOUNT_ID');

    if (empty($this->apiToken) || empty($this->accountId)) {
        $this->markTestSkipped('Cloudflare credentials not configured in .env file.');
    }

    $this->driver = new CloudflareDriver([
        'api_token' => $this->apiToken,
        'account_id' => $this->accountId,
    ]);
});

it('can generate a styled pdf via cloudflare with tailwind', function () {
    $path = getTempPath('cloudflare-tailwind.pdf');

    $html = <<<'HTML'
        <!DOCTYPE html>
        <html>
        <head>
            <script src="https://cdn.tailwindcss.com"></script>
        </head>
        <body class="bg-gray-100 p-8">
            <div class="max-w-md mx-auto bg-white rounded-xl shadow-md overflow-hidden">
                <div class="bg-indigo-600 px-6 py-4">
                    <h1 class="text-white text-2xl font-bold">Invoice #1234</h1>
                    <p class="text-indigo-200 text-sm">Generated via Cloudflare Browser Rendering</p>
                </div>
                <div class="p-6 space-y-4">
                    <div class="flex justify-between border-b pb-2">
                        <span class="text-gray-600">Laravel PDF Package</span>
                        <span class="font-semibold">$99.00</span>
                    </div>
                    <div class="flex justify-between border-b pb-2">
                        <span class="text-gray-600">Support Plan</span>
                        <span class="font-semibold">$49.00</span>
                    </div>
                    <div class="flex justify-between pt-2">
                        <span class="text-lg font-bold">Total</span>
                        <span class="text-lg font-bold text-indigo-600">$148.00</span>
                    </div>
                </div>
            </div>
        </body>
        </html>
        HTML;

    $options = new PdfOptions;
    $options->format = 'A4';

    $this->driver->savePdf($html, null, null, $options, $path);

    expect($path)->toBeFile();
    expect(file_get_contents($path))->toStartWith('%PDF');
});

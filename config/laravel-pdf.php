<?php

use Spatie\LaravelPdf\Jobs\GeneratePdfJob;

return [
    /*
     * The default driver to use for PDF generation.
     * Supported: "browsershot", "cloudflare", "dompdf", "gotenberg", "chrome"
     */
    'driver' => env('LARAVEL_PDF_DRIVER', 'browsershot'),

    /*
     * Configuration for the driver fallback chain.
     */
    'fallback' => [
        /*
         * Ordered list of drivers to try when the default driver fails.
         * Empty array = no fallback (default behavior).
         * Example: ['browsershot', 'dompdf']
         */
        'drivers' => [],

        /*
         * Allowlist of exception FQCNs that trigger a fallback.
         * If non-empty, only these exceptions will cause the next driver to be tried.
         * Takes precedence over `except_exceptions` when both are configured.
         * Example: ['GuzzleHttp\Exception\ConnectException']
         */
        'only_on_exceptions' => [],

        /*
         * Denylist of exception FQCNs that must NOT trigger a fallback.
         * If non-empty (and `only_on_exceptions` is empty), any other exception
         * triggers the fallback while these are re-thrown as-is.
         * Example: ['Illuminate\View\ViewException']
         */
        'except_exceptions' => [],

        /*
         * After a driver fails, mark it unhealthy in the cache so that subsequent
         * requests skip it without re-attempting until the TTL expires.
         */
        'health_cache' => [
            /*
             * Seconds to keep a driver marked as unhealthy. Zero disables the health cache.
             */
            'ttl' => env('LARAVEL_PDF_FALLBACK_HEALTH_TTL', 0),

            /*
             * Cache key prefix.
             */
            'key_prefix' => 'laravel_pdf_driver_health_',

            /*
             * Cache store to use. Null uses the application's default store.
             */
            'store' => env('LARAVEL_PDF_FALLBACK_HEALTH_STORE'),
        ],
    ],

    /*
     * The job class used for queued PDF generation.
     * You can replace this with your own class that extends GeneratePdfJob
     * to customize things like $tries, $timeout, $backoff, or default queue.
     */
    'job' => GeneratePdfJob::class,

    /*
     * Browsershot driver configuration.
     *
     * Requires the spatie/browsershot package:
     * composer require spatie/browsershot
     */
    'browsershot' => [
        /*
         * Configure the paths to Node.js, npm, Chrome, and other binaries.
         * Leave null to use system defaults or Browsershot's auto-detection.
         */
        'node_binary' => env('LARAVEL_PDF_NODE_BINARY'),
        'npm_binary' => env('LARAVEL_PDF_NPM_BINARY'),
        'include_path' => env('LARAVEL_PDF_INCLUDE_PATH'),
        'chrome_path' => env('LARAVEL_PDF_CHROME_PATH'),
        'node_modules_path' => env('LARAVEL_PDF_NODE_MODULES_PATH'),
        'bin_path' => env('LARAVEL_PDF_BIN_PATH'),
        'temp_path' => env('LARAVEL_PDF_TEMP_PATH'),

        /*
         * Other Browsershot configuration options.
         */
        'write_options_to_file' => env('LARAVEL_PDF_WRITE_OPTIONS_TO_FILE', true),
        'no_sandbox' => env('LARAVEL_PDF_NO_SANDBOX', false),
    ],

    /*
     * Cloudflare Browser Rendering driver configuration.
     *
     * Requires a Cloudflare account with the Browser Rendering API enabled.
     * https://developers.cloudflare.com/browser-rendering/
     */
    'cloudflare' => [
        'api_token' => env('CLOUDFLARE_API_TOKEN'),
        'account_id' => env('CLOUDFLARE_ACCOUNT_ID'),
    ],

    /*
     * Gotenberg driver configuration.
     *
     * Requires a running Gotenberg instance (Docker recommended).
     * https://gotenberg.dev
     */
    'gotenberg' => [
        'url' => env('GOTENBERG_URL', 'http://localhost:3000'),
        'username' => env('GOTENBERG_USERNAME'),
        'password' => env('GOTENBERG_PASSWORD'),
    ],

    /*
     * DOMPDF driver configuration.
     *
     * Pure PHP PDF generation — no external binaries required.
     * Requires the dompdf/dompdf package:
     * composer require dompdf/dompdf
     */
    'dompdf' => [
        /*
         * Allow DOMPDF to fetch external resources (images, CSS).
         * Set to true if your HTML references remote URLs.
         */
        'is_remote_enabled' => env('LARAVEL_PDF_DOMPDF_REMOTE_ENABLED', false),

        /*
         * The base path for local file access.
         * Defaults to DOMPDF's built-in chroot setting when null.
         */
        'chroot' => env('LARAVEL_PDF_DOMPDF_CHROOT'),
    ],

    /*
    * WeasyPrint driver configuration.
    *
    * Requires the Weasyprint binary and pontedilana/php-weasyprint package:
    * composer require pontedilana/php-weasyprint
    *
    * @see https://doc.courtbouillon.org/weasyprint/stable/first_steps.html
    */
    'weasyprint' => [
        /*
         * Configure the paths to the Weasyprint binary.
         */
        'binary' => env('LARAVEL_PDF_WEASYPRINT_BINARY', 'weasyprint'),

        /*
         * The timeout (default = 10 seconds)
         */
        'timeout' => 10,
    ],

    /*
     * Chrome PHP driver configuration.
     *
     * Requires the Chrome/Chromium executable and chrome-php/chrome package:
     * composer require chrome-php/chrome
     *
     * @see https://github.com/chrome-php/chrome
     */
    'chrome' => [
        'chrome_binary' => env('LARAVEL_PDF_CHROME_BINARY'),

        'no_sandbox' => env('LARAVEL_PDF_CHROME_NO_SANDBOX', false),

        'startup_timeout' => env('LARAVEL_PDF_CHROME_STARTUP_TIMEOUT', 30),

        'timeout' => env('LARAVEL_PDF_CHROME_TIMEOUT', 30000),

        'user_data_dir' => env('LARAVEL_PDF_CHROME_USER_DATA_DIR'),

        'custom_flags' => [],

        'env_variables' => [],
    ],
];

---
title: Requirements
weight: 3
---

The laravel-pdf package requires **PHP 8.2+** and **Laravel 10+**.

The additional requirements depend on which driver you use:

## DOMPDF driver

The DOMPDF driver uses [dompdf/dompdf](https://github.com/dompdf/dompdf) for pure PHP PDF generation. No external binaries are required. Just install the package:

```bash
composer require dompdf/dompdf
```

## Browsershot driver (default)

The Browsershot driver uses [Browsershot](https://spatie.be/docs/browsershot) under the hood to generate PDFs. You can find the necessary requirements [here](https://spatie.be/docs/browsershot/v4/requirements). This includes Node.js and a Chrome/Chromium binary.

## Gotenberg driver

The Gotenberg driver uses [Gotenberg](https://gotenberg.dev), an open-source Docker-based API for PDF generation. You will need:

- A running Gotenberg instance (easily started with `docker run --rm -p 3000:3000 gotenberg/gotenberg:8`)

## WeasyPrint driver

The WeasyPrint driver uses [WeasyPrint](https://doc.courtbouillon.org/weasyprint/stable/), a Python-based PDF generation tool with excellent CSS Paged Media support. You will need:

- The WeasyPrint binary installed on your system ([installation guide](https://doc.courtbouillon.org/weasyprint/stable/first_steps.html))
- The PHP wrapper package: `composer require pontedilana/php-weasyprint`

## Cloudflare driver

The Cloudflare driver uses [Cloudflare's Browser Rendering API](https://developers.cloudflare.com/browser-rendering/). This driver does not require Node.js or a Chrome binary on your server. You will need:

- A [Cloudflare account](https://dash.cloudflare.com/sign-up) with the Browser Rendering API enabled
- A Cloudflare API token with the appropriate permissions
- Your Cloudflare account ID

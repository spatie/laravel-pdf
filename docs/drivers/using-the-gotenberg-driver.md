---
title: Using the Gotenberg driver
weight: 5
---

The Gotenberg driver uses [Gotenberg](https://gotenberg.dev) to generate PDFs. Gotenberg is an open-source, Docker-based API for converting HTML, Markdown, and Office documents to PDF. It runs as a standalone service using headless Chromium under the hood.

This is a great choice when you want to offload PDF generation to a separate service, especially in containerized or microservice environments.

## Getting started

1. Start a Gotenberg instance using Docker:

```bash
docker run --rm -p 3000:3000 gotenberg/gotenberg:8
```

Or add it to your `docker-compose.yml`:

```yaml
services:
  gotenberg:
    image: gotenberg/gotenberg:8
    ports:
      - "3000:3000"
```

2. Add the following to your `.env` file:

```env
LARAVEL_PDF_DRIVER=gotenberg
GOTENBERG_URL=http://localhost:3000
```

That's it. Your existing PDF code will now use Gotenberg for generation:

```php
use Spatie\LaravelPdf\Facades\Pdf;

Pdf::view('pdfs.invoice', ['invoice' => $invoice])
    ->format('a4')
    ->save('invoice.pdf');
```

## Supported options

The Gotenberg driver supports the following PDF options:

- `format()` — Paper format (a4, letter, etc.)
- `paperSize()` — Custom paper dimensions
- `margins()` — Page margins
- `landscape()` / `orientation()` — Page orientation
- `scale()` — Page rendering scale
- `pageRanges()` — Specific pages to include
- `tagged()` — Generate tagged (accessible) PDF
- `headerView()` / `headerHtml()` — Page headers (repeated on every page)
- `footerView()` / `footerHtml()` — Page footers (repeated on every page)

## Headers and footers

Gotenberg supports repeating headers and footers on every page, just like the Browsershot and Cloudflare drivers. Header and footer HTML is sent as separate HTML documents to Gotenberg.

Note that Gotenberg requires header and footer HTML to be complete HTML documents. You can use CSS classes like `pageNumber` and `totalPages` for dynamic content:

```html
<html>
<head><style>p { font-size: 10px; }</style></head>
<body>
    <p>Page <span class="pageNumber"></span> of <span class="totalPages"></span></p>
</body>
</html>
```

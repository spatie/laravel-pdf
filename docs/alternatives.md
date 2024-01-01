---
title: Alternatives
weight: 5
---

Laravel PDF uses Chrome Headless to generate PDFs. This is a great solution for most use cases. You can use any CSS you want, and it will be rendered correctly. However, generating a PDF this way can be resource intensive.

Here are some alternatives to generate PDFs:

- [laravel-dompdf](https://github.com/barryvdh/laravel-dompdf) - A DOMPDF Wrapper for Laravel
- [wkhtmltopdf](http://wkhtmltopdf.org/) - A command line tool to render HTML into PDF and various image formats using the QT Webkit rendering engine. This is the engine used behind the scenes in Snappy.
- [mPDF](http://www.mpdf1.com/mpdf/index.php) - A PHP class to generate PDF files from HTML with Unicode/UTF-8 and CJK support.
- [FPDF](http://www.fpdf.org/) - A PHP class for generating PDF files on-the-fly.

---
title: Generating PDFs on AWS Lambda
weight: 3
---

Generating PDFs locally can be resource intensive. If you're having to generate a lot of PDFs, or having troubles to install the necessary dependencies on your server, you may want to consider using AWS Lambda to generate your PDFs.

In order to generate PDFs on AWS Lambda, you must install these two packages in your app.

- [hammerstone/sidecar](https://hammerstone.dev/sidecar/docs/main/overview): this allows you to  execute AWS Lambda functions from your Laravel application
- [wnx/sidecar-browsershot](https://github.com/stefanzweifel/sidecar-browsershot): this package contains a version of Browsershot that can run on AWS Lambda via Sidecar

With these two packages installed, you can generate PDFs on AWS Lambda like this:

```php
Pdf::view('pdf.invoice', $data)
    ->onLambda()
    ->save('invoice.pdf');
```

If you want to create all PDFs in your app on Lambda, you can [set it as a default](https://spatie.be/docs/laravel-pdf/v1/basic-usage/setting-defaults) like this:

```php
// typically, in a service provider

Pdf::default()->onLambda();
```

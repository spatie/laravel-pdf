---
title: Saving PDFs to disks
weight: 4
---

Laravel has [a nice filesystem abstraction](https://laravel.com/docs/10.x/filesystem) that allows you to easily save files to any filesystem. It works by configuring a "disk" in `config/filesystems.php` and then using the `Storage` facade to interact with that disk.

Laravel PDF can save PDFs to any disk you have configured in your application. To do so, just use the `disk()` and pass it the name of your configured disk.

Here's an example of saving a PDF to the `s3` disk.

```php
use Spatie\LaravelPdf\Facades\Pdf;

Pdf::view('invoice')
   ->disk('s3')
   ->save('invoice-april-2022.pdf');
```

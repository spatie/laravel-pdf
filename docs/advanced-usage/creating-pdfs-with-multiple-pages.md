---
title: Creating PDFs with multiple pages
weight: 2
---

This package offers a couple of Blade directives to help you create PDFs with multiple pages.

## Setting a page break

To create a PDF with multiple pages, you can use the `@pageBreak` Blade directive in your view. Using this directive will result in a new page being created in the PDF document.

So if you have a view like this...

```blade
<div>
    Page 1
</div>

@pageBreak

<div>
    Page 2
</div>
```

... and you render this view using ...

```php
Pdf::view('view-with-multiple-pages')->save($path);
```

... the resulting PDF will have two pages, one with "Page 1" and one with "Page 2".

## Adding page numbers

To add page numbers to your PDF, you can use the `@pageNumber` and `@totalPages` Blade directive in your view. 

Imagine you have this footer view...

```blade
<div>
    This is page @pageNumber of @totalPages
</div>
```

... and you render this view using ...

```php
Pdf::view('view-with-multiple-pages')->footerView('footer-view')->save($path);
```

... the resulting PDF will have a footer on each page, with the page number and the total number of pages.

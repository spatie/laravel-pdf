---
title: Attaching PDFs to mails
weight: 5
---

A `PdfBuilder` instance implements Laravel's `Illuminate\Contracts\Mail\Attachable` contract. This means you can pass a PDF directly to the `attach` method of a mailable or notification, without first having to save it to disk.

Here's an example of a notification that attaches a generated invoice PDF.

```php
use Illuminate\Notifications\Messages\MailMessage;
use Spatie\LaravelPdf\Facades\Pdf;

public function toMail(object $notifiable): MailMessage
{
    $pdf = Pdf::view('pdfs.invoice', ['invoice' => $this->invoice])
        ->name('invoice.pdf');

    return (new MailMessage)
        ->subject('Your invoice')
        ->line('Please find your invoice attached.')
        ->attach($pdf);
}
```

The filename of the attachment is taken from the PDF's `name()`. The MIME type is set to `application/pdf`.

You can also use it in a mailable.

```php
use Illuminate\Mail\Mailable;
use Spatie\LaravelPdf\Facades\Pdf;

class InvoiceMail extends Mailable
{
    public function __construct(public Invoice $invoice) {}

    public function attachments(): array
    {
        return [
            Pdf::view('pdfs.invoice', ['invoice' => $this->invoice])
                ->name('invoice.pdf'),
        ];
    }
}
```

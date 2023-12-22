<?php

namespace Spatie\LaravelPdf\Commands;

use Illuminate\Console\Command;

class LaravelPdfCommand extends Command
{
    public $signature = 'laravel-pdf';

    public $description = 'My command';

    public function handle(): int
    {
        $this->comment('All done');

        return self::SUCCESS;
    }
}

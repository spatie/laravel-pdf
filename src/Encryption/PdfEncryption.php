<?php

namespace Spatie\LaravelPdf\Encryption;

use Spatie\LaravelPdf\Enums\Permission;

class PdfEncryption
{
    /**
     * @param  array<int, Permission>|null  $permissions  The permissions to grant. When null, every permission is granted.
     */
    public function __construct(
        public string $userPassword = '',
        public ?string $ownerPassword = null,
        public ?array $permissions = null,
    ) {}
}

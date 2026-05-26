<?php

namespace Spatie\LaravelPdf\Enums;

enum Permission: string
{
    case Annotate = 'annot-forms';
    case Assemble = 'assemble';
    case Copy = 'copy';
    case Extract = 'extract';
    case FillForms = 'fill-forms';
    case Modify = 'modify';
    case Print = 'print';
    case PrintHighResolution = 'print-high';

    /** @return array<int, string> */
    public static function all(): array
    {
        return array_map(fn (self $permission) => $permission->value, self::cases());
    }
}

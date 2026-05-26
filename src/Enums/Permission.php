<?php

namespace Spatie\LaravelPdf\Enums;

enum Permission: string
{
    case Print = 'print';

    case Modify = 'modify';

    case Copy = 'copy';

    case Annotate = 'annot-forms';

    case FillForms = 'fill-forms';

    case Extract = 'extract';

    case Assemble = 'assemble';

    case PrintHighResolution = 'print-high';

    /** @return array<int, string> */
    public static function all(): array
    {
        return array_map(fn (self $permission) => $permission->value, self::cases());
    }
}

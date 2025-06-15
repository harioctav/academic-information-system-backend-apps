<?php

namespace App\Enums\Finances;

use App\Traits\EnumsToArray;

enum ProgramType: string
{

    use EnumsToArray;

    case Spp = 'spp';
    case NonSpp = 'non-spp';

    public function label(): string|null
    {
        return match ($this) {
            self::Spp => 'SPP',
            self::NonSpp => 'NON-SPP',
        };
    }
}

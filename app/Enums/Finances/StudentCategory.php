<?php

namespace App\Enums\Finances;

use App\Traits\EnumsToArray;

enum StudentCategory: string
{
    use EnumsToArray;

    case Maba = 'mahasiswa_baru';
    case Mala = 'mahasiswa_lama';
    case AlihKredit = 'alih_kredit';

    public function label(): string|null
    {
        return match ($this) {
            self::Maba => 'Mahasiswa Baru (MABA)',
            self::Mala => 'Mahasiswa Lama (MALA)',
            self::AlihKredit => 'Mahasiswa Alih Kredit (RPL)',
        };
    }
}

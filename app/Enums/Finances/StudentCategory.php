<?php

namespace App\Enums\Finances;

use App\Traits\EnumsToArray;

enum StudentCategory: string
{
  use EnumsToArray;

  case Maba = 'maba';
  case Mala = 'mala';
  case Rpl = 'rpl';

  public function label(): string|null
  {
    return match ($this) {
      self::Maba => 'Mahasiswa Baru',
      self::Mala => 'Mahasiswa Lama',
      self::Rpl => 'RPL',
    };
  }
}

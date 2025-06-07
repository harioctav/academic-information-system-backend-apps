<?php

namespace App\Enums;

use App\Traits\EnumsToArray;

enum ActiveType: string
{
  use EnumsToArray;

  case Active = 'active';
  case InActive = 'non-active';

  public function label(): string|null
  {
    return match ($this) {
      self::Active => 'Aktif',
      self::InActive => 'Tidak Aktif'
    };
  }
}

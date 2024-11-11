<?php

namespace App\Enums;

use App\Traits\EnumsToArray;

enum GeneralConstant: int
{
  use EnumsToArray;

  case Active = 1;
  case InActive = 0;

  public function label(): string|null
  {
    return match ($this) {
      self::Active => 'Aktif',
      self::InActive => 'Tidak Aktif'
    };
  }
}

<?php

namespace App\Enums\Locations;

use App\Traits\EnumsToArray;

enum RegencyType: string
{
  use EnumsToArray;

  case Regency = 'Kabupaten';
  case City = 'Kota';

  public function label(): string|null
  {
    return match ($this) {
      self::Regency => 'Kabupaten',
      self::City => 'Kota',
    };
  }
}

<?php

namespace App\Enums;

use App\Traits\EnumsToArray;

enum GenderType: string
{
  use EnumsToArray;

  case Male = 'male';
  case Female = 'female';
  case Unknown = 'unknown';

  /**
   * Get human-readable label for the enum value
   */
  public function label(): string
  {
    return match ($this) {
      self::Male => 'Laki - Laki',
      self::Female => 'Perempuan',
      self::Unknown => 'Tidak Diketahui',
    };
  }
}

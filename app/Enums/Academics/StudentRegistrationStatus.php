<?php

namespace App\Enums\Academics;

use App\Traits\EnumsToArray;

enum StudentRegistrationStatus: string
{
  use EnumsToArray;

  case Rpl = 'rpl';
  case NonRpl = 'non-rpl';
  case Unknown = 'unknown';

  /**
   * Get human-readable label for the enum value
   */
  public function label(): string
  {
    return match ($this) {
      self::Rpl => 'RPL',
      self::NonRpl => 'NON RPL',
      self::Unknown => 'Tidak Diketahui',
    };
  }
}

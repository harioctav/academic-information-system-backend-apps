<?php

namespace App\Enums;

use App\Traits\EnumsToArray;

enum ReligionType: string
{
  use EnumsToArray;

  case Islam = 'islam';
  case Chatolic = 'katolik';
  case Christian = 'kristen';
  case Hindu = 'hindu';
  case Buddha = 'buddha';
  case Unknown = 'unknown';

  /**
   * Get human-readable label for the enum value
   */
  public function label(): string
  {
    return match ($this) {
      self::Islam => 'Islam',
      self::Chatolic => 'Katolik',
      self::Christian => 'Kristen',
      self::Hindu => 'Hindu',
      self::Buddha => 'Buddha',
      self::Unknown => 'Tidak Diketahui',
    };
  }
}

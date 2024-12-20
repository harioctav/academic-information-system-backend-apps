<?php

namespace App\Enums\Academics;

use App\Traits\EnumsToArray;

enum SubjectStatus: string
{
  use EnumsToArray;

  case Inti = 'I';
  case NonInti = 'N';

  /**
   * Get human-readable label for the enum value
   */
  public function label(): string
  {
    return match ($this) {
      self::Inti => 'Inti',
      self::NonInti => 'Non Inti',
    };
  }
}

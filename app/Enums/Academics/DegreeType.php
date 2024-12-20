<?php

namespace App\Enums\Academics;

use App\Traits\EnumsToArray;

enum DegreeType: string
{
  use EnumsToArray;

  case D3 = 'd3';
  case D4 = 'd4';
  case S1 = 's1';
  case S2 = 's2';

  /**
   * Get human-readable label for the enum value
   */
  public function label(): string
  {
    return match ($this) {
      self::D3 => 'Diploma 3 (D3)',
      self::D4 => 'Diploma 4 (D4)',
      self::S1 => 'Sarjana (S1)',
      self::S2 => 'Magister (S2)',
    };
  }
}

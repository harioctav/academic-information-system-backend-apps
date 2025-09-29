<?php

namespace App\Enums\Finances;

use App\Traits\EnumsToArray;

enum ProgramType: string
{
  use EnumsToArray;

  case SPP = 'spp';
  case NonSPP = 'non-spp';
  case Beasiswa = 'beasiswa';

  public function label(): string|null
  {
    return match ($this) {
      self::SPP => 'SPP',
      self::NonSPP => 'Non SPP',
      self::Beasiswa => 'Beasiswa',
    };
  }
}

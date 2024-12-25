<?php

namespace App\Enums\Academics;

use App\Traits\EnumsToArray;

enum AddressType: string
{
  use EnumsToArray;

  case Domicile = 'domicile';    // Alamat domisili
  case IdCard = 'id_card';

  /**
   * Get human-readable label for the enum value
   */
  public function label(): string
  {
    return match ($this) {
      self::Domicile => 'Alamat Domisili',
      self::IdCard => 'Alamat KTP',
    };
  }
}

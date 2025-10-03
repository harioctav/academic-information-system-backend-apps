<?php

namespace App\Enums\Finances;

use App\Traits\EnumsToArray;

enum PaymentSystem: string
{
  use EnumsToArray;

  case Sipas = 'sipas';
  case NonSipas = 'non-sipas';

  public function label(): string|null
  {
    return match ($this) {
      self::Sipas => 'Sipas',
      self::NonSipas => 'Non Sipas',
    };
  }
}

<?php

namespace App\Enums\Finances;

use App\Traits\EnumsToArray;

enum PaymentPlan: string
{
  use EnumsToArray;

  case Cicil = 'cicil';
  case Lunas = 'lunas';

  public function label(): string|null
  {
    return match ($this) {
      self::Cicil->value => 'Cicil',
      self::Lunas->value => 'Lunas',
    };
  }
}

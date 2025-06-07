<?php

namespace App\Enums\Finances;

use App\Traits\EnumsToArray;

enum PaymentMethod: string
{
  use EnumsToArray;

  case Transfer = 'transfer';
  case Cash = 'cash';

  public function label(): string|null
  {
    return match ($this) {
      self::Transfer->value => 'Transfer',
      self::Cash->value => 'Cash',
    };
  }
}

<?php

namespace App\Enums\Finances;

use App\Traits\EnumsToArray;

enum PaymentStatus: string
{
  use EnumsToArray;

  case Pending = 'pending';
  case Confirmed = 'confirmed';
  case Rejected = 'rejected';

  public function label(): string|null
  {
    return match ($this) {
      self::Pending->value => 'Pending',
      self::Confirmed->value => 'Confirmed',
      self::Rejected->value => 'Rejected',
    };
  }
}

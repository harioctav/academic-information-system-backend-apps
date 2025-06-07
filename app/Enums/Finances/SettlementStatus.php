<?php

namespace App\Enums\Finances;

use App\Traits\EnumsToArray;

enum SettlementStatus: string
{
  use EnumsToArray;

  case Unpaid = 'unpaid';
  case Paid = 'paid';
  case Canceled = 'canceled';

  public function label(): string|null
  {
    return match ($this) {
      self::Unpaid => 'Belum Dibayar',
      self::Paid => 'Sudah Dibayar',
      self::Canceled => 'Dibatalkan'
    };
  }
}

<?php

namespace App\Enums;

use App\Traits\EnumsToArray;

enum WhereOperator: string
{
  use EnumsToArray;

  case In = "in";
  case NotIn = "not in";
  case Between = "between";
  case NotBetween = "not between";
}

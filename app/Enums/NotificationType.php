<?php

namespace App\Enums;

use App\Traits\EnumsToArray;

enum NotificationType: string
{
  use EnumsToArray;

  case Academic = "academic";
  case Permission = "permission";
  case System = "system";
  case Subject = "subject";
}

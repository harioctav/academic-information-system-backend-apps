<?php

namespace App\Enums\Evaluations;

use App\Traits\EnumsToArray;

enum GradeType: string
{
  use EnumsToArray;

  case A = 'A';
  case AMin = 'A-';
  case B = 'B';
  case BMin = 'B-';
  case C = 'C';
  case CMin = 'C-';
  case D = 'D';
  case E = 'E';
}

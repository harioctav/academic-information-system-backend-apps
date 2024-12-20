<?php

namespace App\Enums\Academics;

use App\Traits\EnumsToArray;

enum SubjectNote: string
{
  use EnumsToArray;

  case T = 'TAP';
  case P = 'P';
  case PR = 'PR';
  case E = 'E';
  case BW = 'BW';
  case PS = 'PILIH SALAH SATU';
  case BPR = 'BPR';
  case PRO = 'PRO';
  case TW = 'TW';
  case BPRO = 'BPRO';
  case L = 'L';
}

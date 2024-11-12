<?php

namespace App\Enums;

use App\Traits\EnumsToArray;

enum UserRole: string
{
  use EnumsToArray;

  case SuperAdmin = 'super_admin';
  case SubjectRegisTeam = 'subject_regis_team';
  case FinanceTeam = 'finance_team';
  case StudentRegisTeam = 'student_regis_team';
  case FilingTeam = 'filing_team';

  public function label(): string|null
  {
    return match ($this) {
      self::SuperAdmin => 'Super Admin',
      self::SubjectRegisTeam => 'Tim Regis Matakuliah',
      self::FinanceTeam => 'Tim Keuangan',
      self::StudentRegisTeam => 'Tim Pendaftaran Mahasiswa Baru',
      self::FilingTeam => 'Tim Pemberkasan',
    };
  }
}

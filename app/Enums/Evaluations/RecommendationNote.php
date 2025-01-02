<?php

namespace App\Enums\Evaluations;

use App\Traits\EnumsToArray;

enum RecommendationNote: string
{
  use EnumsToArray;

  case SemesterBerjalan = 'semester_berjalan';
  case Direkomendasikan = 'direkomendasikan';
  case PerluPerbaikan = 'perlu_perbaikan';
  case DalamPerbaikan = 'dalam_perbaikan';
  case SudahDiperbaiki = 'sudah_diperbaiki';
  case RequestPerbaikan = 'permintaan_perbaikan';
  case Lulus = 'lulus';
  case Submitted = 'submit_ke_web_universitas_terbuka';

  /**
   * Get human-readable label for the enum value
   */
  public function label(): string
  {
    return match ($this) {
      self::SemesterBerjalan => 'Semester Berjalan',
      self::Direkomendasikan => 'Direkomendasikan',
      self::PerluPerbaikan => 'Perlu Perbaikan',
      self::DalamPerbaikan => 'Dalam Perbaikan',
      self::SudahDiperbaiki => 'Sudah Diperbaiki',
      self::RequestPerbaikan => 'Permintaan Perbaikan',
      self::Lulus => 'Lulus',
      self::Submitted => 'Sudah Di Submit',
    };
  }
}

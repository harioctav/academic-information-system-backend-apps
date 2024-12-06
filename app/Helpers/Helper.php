<?php

namespace App\Helpers;

use Illuminate\Database\Eloquent\Model;

class Helper
{
  public const All = 'Semua Data';
  public const DefaultPassword = 'password';
  public const NewPassword = 'password@baru123';

  const LocationHierarchy = [
    'village' => [
      'district' => 'district',
      'regency' => 'district.regency',
      'province' => 'district.regency.province'
    ],
    'district' => [
      'regency' => 'regency',
      'province' => 'regency.province'
    ],
    'regency' => [
      'province' => 'province'
    ]
  ];

  public static function handleDeleteFile(
    Model $model,
    string $columns
  ) {
    // 
  }
}

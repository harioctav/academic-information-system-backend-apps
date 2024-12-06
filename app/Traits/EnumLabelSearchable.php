<?php

namespace App\Traits;

trait EnumLabelSearchable
{
  public static function searchByLabelOrValue(string $search): array
  {
    $possibleValues = [];

    foreach (self::cases() as $enum) {
      if (
        str_contains(strtolower($enum->label()), strtolower($search)) ||
        str_contains(strtolower($enum->value), strtolower($search))
      ) {
        $possibleValues[] = $enum->value;
      }
    }

    return $possibleValues;
  }
}

<?php

namespace App\Traits;

trait EnumsToArray
{
  public static function toArray(...$indexes)
  {
    $cases = self::cases();
    $selectedCases = [];

    foreach ($indexes as $index) {
      if (array_key_exists($index, $cases)) {
        $selectedCases[] = $cases[$index]->value;
      }
    }

    if (empty($selectedCases)) {
      return array_map(
        fn(self $enum) => $enum->value,
        $cases
      );
    }

    return $selectedCases;
  }

  public static function toValidation(...$indexes)
  {
    $cases = self::cases();
    $selectedCases = [];

    foreach ($indexes as $index) {
      if (array_key_exists($index, $cases)) {
        $selectedCases[] = $cases[$index]->value;
      }
    }

    if (empty($selectedCases)) {
      return 'in:' . implode(',', array_map(
        fn(self $enum) => $enum->value,
        $cases
      ));
    }

    return 'in:' . implode(',', $selectedCases);
  }
}

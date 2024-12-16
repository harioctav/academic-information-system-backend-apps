<?php

namespace App\Traits;

trait LocationPayload
{
  protected function prepareLocationPayload($payload, $parentField, $parentRepository)
  {
    $parentIdField = $this->getSingularForm($parentField) . '_id';
    $parent = $parentRepository->findOrFail($payload[$parentField]);

    $payload['full_code'] = $parent->full_code . $payload['code'];
    $payload[$parentIdField] = $parent->id;

    return $payload;
  }

  private function getSingularForm($word)
  {
    $irregularPlurals = [
      'categories' => 'category',
      'properties' => 'property',
      'cities' => 'city',
      'countries' => 'country',
      'regencies' => 'regency'
    ];

    if (array_key_exists($word, $irregularPlurals)) {
      return $irregularPlurals[$word];
    }

    // Handle regular plural forms
    if (substr($word, -3) === 'ies') {
      return substr($word, 0, -3) . 'y';
    }

    return rtrim($word, 's');
  }
}

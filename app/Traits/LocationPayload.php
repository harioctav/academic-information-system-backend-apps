<?php

namespace App\Traits;

trait LocationPayload
{
  protected function prepareLocationPayload($payload, $parentField, $parentRepository)
  {
    $parentIdField = $this->getSingularForm($parentField) . '_id';
    $parent = $parentRepository->findOrFail($payload[$parentField]);

    // Pastikan kode disimpan sebagai string untuk mempertahankan angka 0 di depan
    $payload['code'] = (string) $payload['code'];

    // Tentukan full_code berdasarkan hierarki
    if ($parentField === 'provinces') {
      // Untuk regency: full_code = kode provinsi + kode regency
      $payload['full_code'] = $parent->code . $payload['code'];
    } else if ($parentField === 'regencies') {
      // Untuk district: full_code = full_code regency + kode district
      $payload['full_code'] = $parent->full_code . $payload['code'];
    } else if ($parentField === 'districts') {
      // Untuk village: full_code = full_code district + kode village
      $payload['full_code'] = $parent->full_code . $payload['code'];
    } else {
      // Default fallback
      $payload['full_code'] = $parent->full_code ?? $parent->code . $payload['code'];
    }

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

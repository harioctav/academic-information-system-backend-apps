<?php

namespace App\Helpers;

use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Builder;

class SearchHelper
{
  // Add method to sanitize search input
  private static function sanitizeSearchTerm(string $search): string
  {
    if ($search == null):
      return '';
    endif;
    return trim(str_replace(['%', '_'], ['\%', '\_'], $search));
  }

  // Add method to build search conditions
  private static function buildSearchCondition(Builder $query, string $field, string $search): void
  {
    $query->orWhere($field, 'LIKE', "%{$search}%");
  }

  public static function applySearchQuery(
    Builder $query,
    Request $request,
    array $searchableFields = [],
    array $sortableFields = [],
    array $enumFields = [],
    array $combinedFields = [],
    array $relationFields = []
  ): Builder {
    // Sanitize search input early
    $search = $request->has('search') ? self::sanitizeSearchTerm($request->input('search')) : null;

    // Cache enum values to avoid repeated lookups
    $enumValueCache = [];

    // Process relations first for better query optimization
    foreach ($relationFields as $relationField) {
      if ($relationId = $request->input($relationField)) {
        $query->where($relationField, $relationId);
      }
    }

    if ($search) {
      $query->where(function ($query) use ($search, $searchableFields, $combinedFields, $enumFields, &$enumValueCache) {
        // Regular fields
        foreach ($searchableFields as $field) {
          self::buildSearchCondition($query, $field, $search);
        }

        // Enum fields with caching
        foreach ($enumFields as $field => $enumClass) {
          if (!isset($enumValueCache[$enumClass]) && method_exists($enumClass, 'searchByLabelOrValue')) {
            $enumValueCache[$enumClass] = $enumClass::searchByLabelOrValue($search);
          }

          if (!empty($enumValueCache[$enumClass])) {
            $query->orWhereIn($field, $enumValueCache[$enumClass]);
          }
        }

        // Combined fields
        foreach ($combinedFields as $fields) {
          $concatenated = implode(", ' ', ", $fields);
          $query->orWhereRaw("CONCAT({$concatenated}) LIKE ?", ["%{$search}%"]);

          // Only reverse if more than one field
          if (count($fields) > 1) {
            $reversed = implode(", ' ', ", array_reverse($fields));
            $query->orWhereRaw("CONCAT({$reversed}) LIKE ?", ["%{$search}%"]);
          }
        }
      });
    }

    // Apply sorting
    if ($sortField = $request->input('sort_by')) {
      if (in_array($sortField, $sortableFields, true)) {
        $query->orderBy($sortField, $request->input('sort_direction', 'asc'));
      }
    }

    return $query;
  }
}

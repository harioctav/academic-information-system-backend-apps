<?php

namespace App\Helpers;

use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Builder;

class SearchHelper
{
  public static function applySearchQuery(
    Builder $query,
    Request $request,
    array $searchableFields,
    array $sortableFields = [],
    array $combinedFields = []
  ): Builder {
    $search = $request->input('search');

    $query->when($search, function ($query) use ($search, $searchableFields, $combinedFields) {
      $query->where(function ($query) use ($search, $searchableFields, $combinedFields) {
        // Regular field search
        foreach ($searchableFields as $field) {
          $query->orWhere($field, 'LIKE', "%{$search}%");
        }

        // Combined fields search if provided
        if (!empty($combinedFields)) {
          foreach ($combinedFields as $fields) {
            $query->orWhereRaw(
              "CONCAT(" . implode(", ' ', ", $fields) . ") LIKE ?",
              ["%{$search}%"]
            );

            // Reverse order combination
            $reversedFields = array_reverse($fields);
            $query->orWhereRaw(
              "CONCAT(" . implode(", ' ', ", $reversedFields) . ") LIKE ?",
              ["%{$search}%"]
            );
          }
        }
      });
    });

    // Apply Sorting
    $sortField = $request->input('sort_by');
    $sortDirection = $request->input('sort_direction', 'asc');

    if ($sortField && in_array($sortField, $sortableFields)) {
      $query->orderBy($sortField, $sortDirection);
    }

    return $query;
  }
}

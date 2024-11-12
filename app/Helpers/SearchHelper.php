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
    array $sortableFields = []
  ): Builder {
    // Apply Search
    $search = $request->input('search');
    $query->when($search, function ($query) use ($search, $searchableFields) {
      foreach ($searchableFields as $field) {
        $query->orWhere($field, 'LIKE', "%{$search}%");
      }
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

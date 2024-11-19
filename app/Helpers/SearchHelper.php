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
    $search = $request->input('search');

    $query->when($search, function ($query) use ($search, $searchableFields) {
      $query->where(function ($query) use ($search, $searchableFields) {
        // Regular field search
        foreach ($searchableFields as $field) {
          $query->orWhere($field, 'LIKE', "%{$search}%");
        }

        // Combined type and name search (both orders)
        $query->orWhereRaw("CONCAT(type, ' ', name) LIKE ?", ["%{$search}%"])
          ->orWhereRaw("CONCAT(name, ' ', type) LIKE ?", ["%{$search}%"]);
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

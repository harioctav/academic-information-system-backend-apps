<?php

namespace App\Helpers;

use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class SearchHelper
{
  private static function sanitizeSearchTerm(?string $search): string
  {
    return $search ? trim(str_replace(['%', '_'], ['\%', '\_'], $search)) : '';
  }

  // Old function
  // private static function buildSearchCondition(Builder $query, string $field, string $search): void
  // {
  //   $cacheKey = "search_{$field}_{$search}";
  //   Cache::remember($cacheKey, now()->addHours(1), function () use ($query, $field, $search) {
  //     $query->orWhere($field, 'LIKE', "%{$search}%");
  //   });
  // }

  private static function buildSearchCondition(Builder $query, string $field, string $search): void
  {
    $cacheKey = "search_{$field}_{$search}";

    Cache::remember($cacheKey, now()->addHours(1), function () use ($query, $field, $search) {
      if (str_contains($field, '.')) {
        [$relation, $column] = explode('.', $field, 2);
        $query->orWhereHas($relation, function ($q) use ($column, $search) {
          $q->where($column, 'like', "%{$search}%");
        });
      } else {
        $query->orWhere($field, 'LIKE', "%{$search}%");
      }
    });
  }

  public static function applySearchQuery(
    Builder $query,
    Request $request,
    array $searchableFields = [],
    array $sortableFields = [],
    array $enumFields = [],
    array $combinedFields = [],
    array $filterFields = [],
    array $relationFilters = [],
    bool $useLazyLoading = false,
    int $chunkSize = 1000
  ): Builder {
    $search = self::sanitizeSearchTerm($request->input('search'));
    $enumValueCache = [];

    if ($useLazyLoading) {
      $query->lazy();
    }

    collect($filterFields)->each(function ($relationField) use ($query, $request) {
      if ($relationId = $request->input($relationField)) {
        $query->where($relationField, $relationId);
      }
    });

    // Handling relation filters
    collect($relationFilters)->each(function ($field, $relation) use ($query, $request) {
      if ($request->has($relation)) {
        $query->whereHas($relation, function ($q) use ($field, $request, $relation) {
          $q->where($field, $request->input($relation));
        });
      }
    });

    if ($search) {
      $query->where(function ($query) use ($search, $searchableFields, $combinedFields, $enumFields, &$enumValueCache) {
        // Regular fields with cache
        collect($searchableFields)->each(function ($field) use ($query, $search) {
          self::buildSearchCondition($query, $field, $search);
        });

        // Enum fields with optimized caching
        collect($enumFields)->each(function ($enumClass, $field) use ($query, $search, &$enumValueCache) {
          $enumCacheKey = "enum_{$enumClass}_{$search}";
          $enumValueCache[$enumClass] = Cache::remember($enumCacheKey, now()->addDay(), function () use ($enumClass, $search) {
            return method_exists($enumClass, 'searchByLabelOrValue')
              ? $enumClass::searchByLabelOrValue($search)
              : [];
          });

          if (!empty($enumValueCache[$enumClass])) {
            $query->orWhereIn($field, $enumValueCache[$enumClass]);
          }
        });

        // Optimized combined fields processing
        collect($combinedFields)->each(function ($fields) use ($query, $search) {
          $concatenated = implode(", ' ', ", $fields);
          $query->orWhereRaw("CONCAT({$concatenated}) LIKE ?", ["%{$search}%"]);

          if (count($fields) > 1) {
            $reversed = implode(", ' ', ", array_reverse($fields));
            $query->orWhereRaw("CONCAT({$reversed}) LIKE ?", ["%{$search}%"]);
          }
        });
      });
    }

    // Optimized sorting
    if ($sortField = $request->input('sort_by')) {
      if (in_array($sortField, $sortableFields, true)) {
        $direction = strtolower($request->input('sort_direction', 'asc'));
        $query->orderBy($sortField, in_array($direction, ['asc', 'desc']) ? $direction : 'asc');
      }
    }

    // Implement chunking for large datasets
    if ($request->has('chunk')) {
      $query->chunk($chunkSize, function ($records) {
        // Process records
        return true;
      });
    }

    return $query;
  }
}

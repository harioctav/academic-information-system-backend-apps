<?php

namespace App\Http\Controllers\Api\Options;

use App\Helpers\SearchHelper;
use App\Http\Controllers\Controller;
use App\Http\Resources\Locations\DistrictResource;
use App\Http\Resources\Locations\ProvinceResource;
use App\Http\Resources\Locations\RegencyResource;
use App\Http\Resources\Locations\VillageResource;
use App\Services\District\DistrictService;
use App\Services\Province\ProvinceService;
use App\Services\Regency\RegencyService;
use App\Services\Village\VillageService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SelectRegionController extends Controller
{
  public function __construct(
    protected ProvinceService $provinceService,
    protected RegencyService $regencyService,
    protected DistrictService $districtService,
    protected VillageService $villageService
  ) {
    # code...
  }

  public function provinces(Request $request)
  {
    $query = SearchHelper::applySearchQuery(
      query: $this->provinceService->query(),
      request: $request,

      searchableFields: [
        'code',
        'name'
      ],
      sortableFields: [
        'name',
        'code',
        'created_at',
        'updated_at'
      ],
      filterFields: [
        'id'
      ]
    );

    return ProvinceResource::collection(
      $query->latest()->paginate($request->input('per_page', 5))
    );
  }

  public function regencies(Request $request)
  {
    $query = SearchHelper::applySearchQuery(
      query: $this->regencyService->getWhere(
        [
          'province_id' => $request->province_id
        ],
        '*',
        '=',
        'name',
        'asc'
      ),
      request: $request,
      searchableFields: [
        'name',
        'code',
        'type',
        'full_code'
      ],
      sortableFields: [
        'name',
        'code',
        'created_at',
        'updated_at'
      ],
      combinedFields: [
        [
          'type',
          'name'
        ]
      ],
      filterFields: [
        'province_id',
        'type',
        'id'
      ]
    );

    return RegencyResource::collection(
      $query->latest()->paginate($request->input('per_page', 5))
    );
  }

  public function districts(Request $request)
  {
    $query = SearchHelper::applySearchQuery(
      query: $this->districtService->getWhere(
        ['regency_id' => $request->regency_id],
        '*',
        '=',
        'name',
        'asc'
      ),
      request: $request,
      searchableFields: [

        'name',
        'code',
        'full_code'
      ],
      sortableFields: [
        'name',
        'code',
        'created_at',
        'updated_at'
      ],
      filterFields: [
        'regency_id',
        'id'
      ]
    );

    return DistrictResource::collection(
      $query->latest()->paginate($request->input('per_page', 5))
    );
  }

  public function villages(Request $request)
  {
    $query = SearchHelper::applySearchQuery(
      query: $this->villageService->getWhere(
        ['district_id' => $request->district_id],
        '*',
        '=',
        'name',
        'asc'
      ),
      request: $request,
      searchableFields: [
        'name',
        'code',
        'full_code',
        'pos_code'
      ],
      sortableFields: [
        'name',
        'code',
        'created_at',
        'updated_at'
      ],
      filterFields: [
        'district_id',
        'id'
      ]
    );

    return VillageResource::collection(
      $query->latest()->paginate($request->input('per_page', 5))
    );
  }

  public function village(int $id): JsonResponse
  {
    return $this->villageService->findById($id);
  }
}

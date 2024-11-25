<?php

namespace App\Http\Controllers\Api\Locations;

use App\Helpers\SearchHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Locations\DistrictRequest;
use App\Http\Resources\Locations\DistrictResource;
use App\Models\District;
use App\Services\District\DistrictService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DistrictController extends Controller
{
  /**
   * The instance used by this controller.
   */
  protected $districtService;

  /**
   * Create a new controller instance.
   *
   * @return void
   */
  public function __construct(
    DistrictService $districtService
  ) {
    $this->districtService = $districtService;
  }

  /**
   * Display a listing of the resource.
   */
  public function index(Request $request)
  {
    $query = SearchHelper::applySearchQuery(
      query: $this->districtService->query(),
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
      relationFields: [
        'regency_id'
      ]
    );

    if ($request->has('province_id')) {
      $query->whereHas('regency', function ($sub) use ($request) {
        $sub->where('province_id', $request->province_id);
      });
    }

    return DistrictResource::collection(
      $query->latest()->paginate($request->input('per_page', 5))
    );
  }

  /**
   * Store a newly created resource in storage.
   */
  public function store(DistrictRequest $request): JsonResponse
  {
    return $this->districtService->handleStore($request);
  }

  /**
   * Display the specified resource.
   */
  public function show(District $district): DistrictResource
  {
    return new DistrictResource($district);
  }

  /**
   * Update the specified resource in storage.
   */
  public function update(DistrictRequest $request, District $district): JsonResponse
  {
    return $this->districtService->handleUpdate($request, $district);
  }

  /**
   * Remove the specified resource from storage.
   */
  public function destroy(District $district): JsonResponse
  {
    return $this->districtService->handleDelete($district);
  }

  /**
   * Remove multiple resources from storage.
   */
  public function bulkDestroy(Request $request): JsonResponse
  {
    $request->validate([
      'ids' => 'required|array',
      'ids.*' => 'exists:districts,uuid'
    ]);

    return $this->districtService->handleBulkDelete($request->ids);
  }
}

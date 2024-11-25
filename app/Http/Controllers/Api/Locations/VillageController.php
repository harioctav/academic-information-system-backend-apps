<?php

namespace App\Http\Controllers\Api\Locations;

use App\Helpers\SearchHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Locations\VillageRequest;
use App\Http\Resources\Locations\VillageResource;
use App\Models\Village;
use App\Services\Village\VillageService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class VillageController extends Controller
{
  protected $villageService;

  /**
   * Create a new controller instance.
   *
   * @return void
   */
  public function __construct(
    VillageService $villageService,
  ) {
    $this->villageService = $villageService;
  }

  /**
   * Display a listing of the resource.
   */
  public function index(Request $request)
  {
    $query = SearchHelper::applySearchQuery(
      query: $this->villageService->query(),
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
      relationFields: [
        'district_id'
      ]
    );

    return VillageResource::collection(
      $query->latest()->paginate($request->input('per_page', 5))
    );
  }

  /**
   * Store a newly created resource in storage.
   */
  public function store(VillageRequest $request): JsonResponse
  {
    return $this->villageService->handleStore($request);
  }

  /**
   * Display the specified resource.
   */
  public function show(Village $village): VillageResource
  {
    return new VillageResource($village);
  }

  /**
   * Update the specified resource in storage.
   */
  public function update(VillageRequest $request, Village $village): JsonResponse
  {
    return $this->villageService->handleUpdate($request, $village);
  }

  /**
   * Remove the specified resource from storage.
   */
  public function destroy(Village $village): JsonResponse
  {
    return $this->villageService->handleDelete($village);
  }

  /**
   * Remove multiple resources from storage.
   */
  public function bulkDestroy(Request $request): JsonResponse
  {
    $request->validate([
      'ids' => 'required|array',
      'ids.*' => 'exists:villages,uuid'
    ]);

    return $this->villageService->handleBulkDelete($request->ids);
  }
}

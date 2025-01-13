<?php

namespace App\Http\Controllers\Api\Locations;

use App\Helpers\SearchHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\Locations\RegencyRequest;
use App\Http\Resources\Locations\RegencyResource;
use App\Models\Regency;
use App\Services\Regency\RegencyService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RegencyController extends Controller
{
  /**
   * The regencyService instance used by this controller.
   */
  protected $regencyService;

  /**
   * Create a new controller instance.
   *
   * @return void
   */
  public function __construct(
    RegencyService $regencyService
  ) {
    $this->regencyService = $regencyService;
  }

  /**
   * Display a listing of the resource.
   */
  public function index(Request $request)
  {
    $query = SearchHelper::applySearchQuery(
      $this->regencyService->query(),
      $request,
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
        'type'
      ]
    );

    $perPage = $request->input('per_page', 5);
    $result = $query->latest();

    return RegencyResource::collection(
      $perPage == -1 ? $result->get() : $result->paginate($perPage)
    );
  }

  /**
   * Store a newly created resource in storage.
   */
  public function store(RegencyRequest $request): JsonResponse
  {
    return $this->regencyService->handleStore($request);
  }

  /**
   * Display the specified resource.
   */
  public function show(Regency $regency)
  {
    return new RegencyResource($regency);
  }

  /**
   * Update the specified resource in storage.
   */
  public function update(RegencyRequest $request, Regency $regency): JsonResponse
  {
    return $this->regencyService->handleUpdate($request, $regency);
  }

  /**
   * Remove the specified resource from storage.
   */
  public function destroy(Regency $regency): JsonResponse
  {
    return $this->regencyService->handleDelete($regency);
  }

  /**
   * Remove multiple resources from storage.
   */
  public function bulkDestroy(Request $request): JsonResponse
  {
    $request->validate([
      'ids' => 'required|array',
      'ids.*' => 'exists:regencies,uuid'
    ]);

    return $this->regencyService->handleBulkDelete($request->ids);
  }
}

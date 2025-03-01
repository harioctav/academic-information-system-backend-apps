<?php

namespace App\Http\Controllers\Api\Locations;

use App\Helpers\SearchHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\Locations\ProvinceRequest;
use App\Http\Resources\Locations\ProvinceResource;
use App\Models\Province;
use App\Services\Province\ProvinceService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProvinceController extends Controller
{
  /**
   * The ProvinceService instance used by this controller.
   */
  protected $provinceService;

  /**
   * Create a new controller instance.
   *
   * @return void
   */
  public function __construct(ProvinceService $provinceService)
  {
    $this->provinceService = $provinceService;
  }

  /**
   * Display a listing of the resource.
   */
  public function index(Request $request)
  {
    $query = SearchHelper::applySearchQuery(
      $this->provinceService->query(),
      $request,
      searchableFields: [
        'code',
        'name',
      ],
      sortableFields: [
        'name',
        'code',
        'created_at',
        'updated_at'
      ]
    );

    $perPage = $request->input('per_page', 5);
    $result = $query->latest();

    return ProvinceResource::collection(
      $perPage == -1 ? $result->get() : $result->paginate($perPage)
    );
  }

  /**
   * Store a newly created resource in storage.
   */
  public function store(ProvinceRequest $request): JsonResponse
  {
    return $this->provinceService->handleStore($request);
  }

  /**
   * Display the specified resource.
   */
  public function show(Province $province): ProvinceResource
  {
    return new ProvinceResource($province);
  }

  /**
   * Update the specified resource in storage.
   */
  public function update(ProvinceRequest $request, Province $province): JsonResponse
  {
    return $this->provinceService->handleUpdate($request, $province);
  }

  /**
   * Remove the specified resource from storage.
   */
  public function destroy(Province $province): JsonResponse
  {
    return $this->provinceService->handleDelete($province);
  }

  /**
   * Remove multiple resources from storage.
   */
  public function bulkDestroy(Request $request): JsonResponse
  {
    $request->validate([
      'ids' => 'required|array',
      'ids.*' => 'exists:provinces,uuid'
    ]);

    return $this->provinceService->handleBulkDelete($request->ids);
  }
}

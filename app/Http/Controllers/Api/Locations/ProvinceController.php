<?php

namespace App\Http\Controllers\Api\Locations;

use App\Helpers\SearchHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Locations\ProvinceRequest;
use App\Http\Resources\Locations\ProvinceResource;
use App\Models\Province;
use App\Services\Province\ProvinceService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;

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
      searchableFields: ['name', 'code'],
      sortableFields: ['name', 'code', 'created_at']
    );

    return ProvinceResource::collection(
      $query->paginate($request->input('per_page', 5))
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
  public function show(Province $province)
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
}

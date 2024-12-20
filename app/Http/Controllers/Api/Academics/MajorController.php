<?php

namespace App\Http\Controllers\Api\Academics;

use App\Helpers\SearchHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\Academics\MajorRequest;
use App\Http\Resources\Academics\MajorResource;
use App\Models\Major;
use App\Services\Major\MajorService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MajorController extends Controller
{
  /**
   * The MajorService instance used by this controller.
   */
  protected $majorService;

  /**
   * Create a new controller instance.
   *
   * @return void
   */
  public function __construct(
    MajorService $majorService
  ) {
    $this->majorService = $majorService;
  }

  /**
   * Display a listing of the resource.
   */
  public function index(Request $request)
  {
    $query = SearchHelper::applySearchQuery(
      query: $this->majorService->query(),
      request: $request,
      searchableFields: [
        'code',
        'name',
      ],
      sortableFields: [
        'code',
        'name',
        'created_at',
        'updated_at'
      ],
      relationFields: [
        'degree'
      ]
    );

    return MajorResource::collection(
      $query->latest()->paginate($request->input('per_page', 5))
    );
  }

  /**
   * Store a newly created resource in storage.
   */
  public function store(MajorRequest $request): JsonResponse
  {
    return $this->majorService->handleStore($request);
  }

  /**
   * Display the specified resource.
   */
  public function show(Major $major): MajorResource
  {
    return new MajorResource($major);
  }

  /**
   * Update the specified resource in storage.
   */
  public function update(MajorRequest $request, Major $major): JsonResponse
  {
    return $this->majorService->handleUpdate($request, $major);
  }

  /**
   * Remove the specified resource from storage.
   */
  public function destroy(Major $major): JsonResponse
  {
    return $this->majorService->handleDelete($major);
  }

  /**
   * Remove multiple resources from storage.
   */
  public function bulkDestroy(Request $request): JsonResponse
  {
    $request->validate([
      'ids' => 'required|array',
      'ids.*' => 'exists:majors,uuid'
    ]);

    return $this->majorService->handleBulkDelete($request->ids);
  }
}

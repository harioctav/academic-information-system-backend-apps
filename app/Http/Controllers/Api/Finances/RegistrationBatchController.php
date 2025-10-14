<?php

namespace App\Http\Controllers\Api\Finances;

use App\Http\Controllers\Controller;
use App\Helpers\SearchHelper;
use App\Http\Requests\Finances\RegistrationBatchRequest;
use App\Http\Resources\Finances\RegistrationBatchResource;
use App\Services\RegistrationBatch\RegistrationBatchService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Models\RegistrationBatch;
use App\Enums\GeneralConstant;

class RegistrationBatchController extends Controller
{
  /**
   * The Registration Batch Service instance used by this controller.
   */
  protected $registrationBatchService;

  /**
   * Create a new controller instance.
   *
   * @return void
   */
  public function __construct(
    RegistrationBatchService $registrationBatchService
  ) {
    $this->registrationBatchService = $registrationBatchService;
  }

  # ==================== PUBIC ==================== #

  public function active(Request $request)
  {
    $query = SearchHelper::applySearchQuery(
      query: $this->registrationBatchService->query()
        ->where('status', GeneralConstant::Active),
      request: $request,
      searchableFields: [
        'name',
        'description',
        'notes'
      ],
      sortableFields: [
        'name',
        'start_date',
        'end_date',
        'created_at'
      ],
      filterFields: [
        'status'
      ],
      enumFields: [
        'status' => GeneralConstant::class
      ]
    );

    $perPage = $request->input('per_page', 10);
    $result = $query->latest();

    return RegistrationBatchResource::collection(
      $perPage == -1 ? $result->get() : $result->paginate($perPage)
    );
  }

  public function activeDetail(RegistrationBatch $registrationBatch): RegistrationBatchResource
  {
    return new RegistrationBatchResource($registrationBatch);
  }

  # ==================== PUBIC ==================== #


  /**
   * Display a listing of the resource.
   */
  public function index(Request $request)
  {
    $query = SearchHelper::applySearchQuery(
      query: $this->registrationBatchService->query(),
      request: $request,
      searchableFields: [
        'name',
        'description',
        'notes'
      ],
      sortableFields: [
        'name',
        'start_date',
        'end_date',
        'created_at'
      ],
      filterFields: [
        'status'
      ],
      enumFields: [
        'status' => GeneralConstant::class
      ]
    );

    $perPage = $request->input('per_page', 10);
    $result = $query->latest();

    return RegistrationBatchResource::collection(
      $perPage == -1 ? $result->get() : $result->paginate($perPage)
    );
  }

  /**
   * Store a newly created resource in storage.
   */
  public function store(RegistrationBatchRequest $request): JsonResponse
  {
    return $this->registrationBatchService->handleStore($request);
  }

  /**
   * Display the specified resource.
   */
  public function show(RegistrationBatch $registrationBatch): RegistrationBatchResource
  {
    return new RegistrationBatchResource($registrationBatch);
  }

  /**
   * Update the specified resource in storage.
   */
  public function update(RegistrationBatchRequest $request, RegistrationBatch $registrationBatch): JsonResponse
  {
    return $this->registrationBatchService->handleUpdate($request, $registrationBatch);
  }

  /**
   * Remove the specified resource from storage.
   */
  public function destroy(RegistrationBatch $registrationBatch): JsonResponse
  {
    return $this->registrationBatchService->handleDelete($registrationBatch);
  }

  /**
   * Update the status of the specified resource in storage.
   */
  public function status(RegistrationBatch $registrationBatch): JsonResponse
  {
    return $this->registrationBatchService->handleChangeStatus($registrationBatch);
  }

  /**
   * Remove multiple resources from storage.
   */
  public function bulkDestroy(Request $request): JsonResponse
  {
    $request->validate([
      'ids' => 'required|array',
      'ids.*' => 'exists:registration_batches,uuid'
    ]);

    return $this->registrationBatchService->handleBulkDelete($request->ids);
  }
}

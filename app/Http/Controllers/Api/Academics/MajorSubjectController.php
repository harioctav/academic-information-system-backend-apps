<?php

namespace App\Http\Controllers\Api\Academics;

use App\Helpers\SearchHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\Academics\MajorSubjectRequest;
use App\Http\Resources\Academics\MajorSubjectResource;
use App\Models\Major;
use App\Models\MajorSubject;
use App\Services\MajorSubject\MajorSubjectService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MajorSubjectController extends Controller
{
  /**
   * The MajorSubjectService instance used by this controller.
   *
   * @var \App\Services\MajorSubject\MajorSubjectService
   */
  protected $majorSubjectService;

  /**
   * Create a new controller instance.
   *
   * @return void
   */
  public function __construct(
    MajorSubjectService $majorSubjectService
  ) {
    $this->majorSubjectService = $majorSubjectService;
  }

  /**
   * Display a listing of the resource.
   */
  public function index(Request $request, Major $major)
  {
    $baseQuery =
      $this->majorSubjectService->query()
      ->join('subjects', 'major_has_subjects.subject_id', '=', 'subjects.id')
      ->select('major_has_subjects.*')
      ->where('major_id', $major->id);

    $query = SearchHelper::applySearchQuery(
      query: $baseQuery,
      request: $request,
      searchableFields: [
        'subjects.code',
        'subjects.name',
        'subjects.course_credit',
        'semester'
      ],
      sortableFields: [
        'subjects.code',
        'subjects.name',
        'major_has_subjects.semester',
        'major_has_subjects.created_at',
        'major_has_subjects.updated_at',
      ],
      relationFields: [
        'semester'
      ]
    );

    return MajorSubjectResource::collection(
      $query->oldest('major_has_subjects.semester')->paginate($request->input('per_page', 5))
    );
  }

  /**
   * Store a newly created resource in storage.
   */
  public function store(Major $major, MajorSubjectRequest $request): JsonResponse
  {
    return $this->majorSubjectService->handleStore($major, $request);
  }

  /**
   * Display the specified resource.
   */
  public function show(Major $major, MajorSubject $majorSubject): MajorSubjectResource
  {
    return new MajorSubjectResource($majorSubject);
  }

  /**
   * Update the specified resource in storage.
   */
  public function update(Major $major, MajorSubjectRequest $request, MajorSubject $majorSubject): JsonResponse
  {
    return $this->majorSubjectService->handleUpdate($request, $majorSubject);
  }

  /**
   * Remove the specified resource from storage.
   */
  public function destroy(Major $major, MajorSubject $majorSubject)
  {
    return $this->majorSubjectService->handleDestroy($major, $majorSubject);
  }

  /**
   * Remove multiple resources from storage.
   */
  public function bulkDestroy(Major $major, Request $request): JsonResponse
  {
    $request->validate([
      'ids' => 'required|array',
      'ids.*' => 'exists:major_has_subjects,uuid'
    ]);

    return $this->majorSubjectService->handleBulkDelete($request->ids);
  }
}

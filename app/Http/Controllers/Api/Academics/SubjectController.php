<?php

namespace App\Http\Controllers\Api\Academics;

use App\Helpers\SearchHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\Academics\SubjectRequest;
use App\Http\Resources\Academics\SubjectResource;
use App\Models\Student;
use App\Models\Subject;
use App\Services\Subject\SubjectService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SubjectController extends Controller
{
  /**
   * The SubjectService instance used by this controller.
   */
  protected $subjectService;

  /**
   * Create a new controller instance.
   *
   * @return void
   */
  public function __construct(
    SubjectService $subjectService
  ) {
    $this->subjectService = $subjectService;
  }

  /**
   * Display a listing of the resource.
   */
  public function index(Request $request)
  {
    $query = SearchHelper::applySearchQuery(
      query: $this->subjectService->query(),
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
        'subject_status'
      ]
    );

    return SubjectResource::collection(
      $query->latest()->paginate($request->input('per_page', 5))
    );
  }

  /**
   * Store a newly created resource in storage.
   */
  public function store(SubjectRequest $request): JsonResponse
  {
    return $this->subjectService->handleStore($request);
  }

  /**
   * Display the specified resource.
   */
  public function show(Subject $subject): SubjectResource
  {
    return new SubjectResource($subject);
  }

  /**
   * Update the specified resource in storage.
   */
  public function update(SubjectRequest $request, Subject $subject): JsonResponse
  {
    return $this->subjectService->handleUpdate($request, $subject);
  }

  /**
   * Remove the specified resource from storage.
   */
  public function destroy(Subject $subject): JsonResponse
  {
    return $this->subjectService->handleDelete($subject);
  }

  /**
   * Remove multiple resources from storage.
   */
  public function bulkDestroy(Request $request): JsonResponse
  {
    $request->validate([
      'ids' => 'required|array',
      'ids.*' => 'exists:subjects,uuid'
    ]);

    return $this->subjectService->handleBulkDelete($request->ids);
  }

  public function condition(Request $request, Student $student)
  {
    $query = SearchHelper::applySearchQuery(
      query: $this->subjectService->getSubjectsForStudent(
        student: $student
      ),
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
        'subject_status'
      ]
    );

    return SubjectResource::collection(
      $query->paginate($request->input('per_page', 5))
    );
  }
}

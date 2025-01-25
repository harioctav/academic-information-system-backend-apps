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
      filterFields: [
        'subject_status'
      ]
    );

    $perPage = $request->input('per_page', 5);
    $result = $query->latest();

    return SubjectResource::collection(
      $perPage == -1 ? $result->get() : $result->paginate($perPage)
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

  public function subjectListRecommendations(Request $request, Student $student)
  {
    $query = $this->subjectService->getListSubjectRecommendations($student);

    // Add semester filter
    if ($request->has('semester')) {
      $query->where('major_has_subjects.semester', $request->semester);
    }

    // Add the recommendation filtering logic
    $query->leftJoin('recommendations', function ($join) use ($student) {
      $join->on('subjects.id', '=', 'recommendations.subject_id')
        ->where('recommendations.student_id', '=', $student->id);
    });

    // Only show subjects that either:
    // 1. Don't have recommendations OR
    // 2. Have grade 'E'
    if (!$request->has('grade_filter')) {
      $query->where(function ($query) {
        $query->whereNull('recommendations.id')
          ->orWhereHas('grades', function ($q) {
            $q->where('grade', 'E');
          });
      });
    }

    // Add grade filter if provided
    if ($request->has('grade_filter')) {
      $query->whereHas('grades', function ($q) use ($request) {
        $q->where('grade', $request->grade_filter);
      });
    }

    $query = SearchHelper::applySearchQuery(
      query: $query,
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
      filterFields: [
        'subject_status'
      ]
    );

    $perPage = $request->input('per_page', 20);
    $result = $query->latest();

    return SubjectResource::collection(
      $perPage == -1 ? $result->get() : $result->paginate($perPage)
    );
  }

  public function subjectListGrades(Request $request, Student $student)
  {
    $query = $this->subjectService->getListSubjectGrades($student);

    $query = SearchHelper::applySearchQuery(
      query: $query,
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
      filterFields: [
        'subject_status'
      ]
    );

    $perPage = $request->input('per_page', 5);
    $result = $query->latest();

    return SubjectResource::collection(
      $perPage == -1 ? $result->get() : $result->paginate($perPage)
    );
  }
}

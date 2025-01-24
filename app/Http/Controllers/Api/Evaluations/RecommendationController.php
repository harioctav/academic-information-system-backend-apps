<?php

namespace App\Http\Controllers\Api\Evaluations;

use App\Enums\GeneralConstant;
use App\Helpers\SearchHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\Evaluations\RecommendationRequest;
use App\Http\Resources\Academics\StudentResource;
use App\Http\Resources\Evaluations\RecommendationResource;
use App\Models\Recommendation;
use App\Models\Student;
use App\Services\Recommendation\RecommendationService;
use App\Services\Student\StudentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RecommendationController extends Controller
{
  protected $recommendationService, $studentService;

  /**
   * Create a new controller instance.
   *
   * @return void
   */
  public function __construct(
    StudentService $studentService,
    RecommendationService $recommendationService,
  ) {
    $this->studentService = $studentService;
    $this->recommendationService = $recommendationService;
  }

  /**
   * Display a listing of the resource.
   */
  public function index(Request $request)
  {
    $query = SearchHelper::applySearchQuery(
      query: $this->studentService->query()
        ->active()
        ->with([
          'domicileAddress.village',
          'idCardAddress.village'
        ]),
      request: $request,
      searchableFields: [
        'nim',
        'nik',
        'name',
        'email',
        'phone',
        'parent_name',
        'parent_phone_number'
      ],
      sortableFields: [
        'nim',
        'name',
        'email',
        'created_at',
        'updated_at'
      ],
      filterFields: [
        'major_id',
        'gender',
        'religion',
        'status_registration'
      ]
    );

    $perPage = $request->input('per_page', 5);
    $result = $query->latest();

    return StudentResource::collection(
      $perPage == -1 ? $result->get() : $result->paginate($perPage)
    );
  }

  /**
   * Store a newly created resource in storage.
   */
  public function store(RecommendationRequest $request, Student $student): JsonResponse
  {
    return $this->recommendationService->handleStore($request, $student);
  }

  /**
   * Display the specified resource.
   */
  public function show(Student $student, Request $request)
  {
    $query = SearchHelper::applySearchQuery(
      query: $this->recommendationService->getWhere(
        wheres: [
          'recommendations.student_id' => $student->id
        ],
        orderBy: 'semester',
        orderByType: 'asc'
      )->with([
        'subject:id,code,name,course_credit',
        'subject.grades' => function ($query) use ($student) {
          $query->where('student_id', $student->id)
            ->select('id', 'subject_id', 'grade', 'quality', 'mutu');
        }
      ]),
      request: $request,
      searchableFields: [
        'semester',
        'exam_period',
      ],
      sortableFields: [
        'semester',
        'created_at',
        'updated_at'
      ],
      filterFields: [
        'recommendation_note',
        'semester'
      ]
    );

    $perPage = $request->input('per_page', 5);
    $result = $query->latest();

    return RecommendationResource::collection(
      $perPage == -1 ? $result->get() : $result->paginate($perPage)
    );
  }

  /**
   * Update the specified resource in storage.
   */
  public function update(RecommendationRequest $request, Recommendation $recommendation): JsonResponse
  {
    return $this->recommendationService->handleUpdate($request, $recommendation);
  }

  /**
   * Remove the specified resource from storage.
   */
  public function destroy(Recommendation $recommendation)
  {
    return $this->recommendationService->handleDelete($recommendation);
  }

  /**
   * Remove multiple resources from storage.
   */
  public function bulkDestroy(Request $request): JsonResponse
  {
    $request->validate([
      'ids' => 'required|array',
      'ids.*' => 'exists:recommendations,uuid'
    ]);

    return $this->recommendationService->handleBulkDelete($request->ids);
  }
}

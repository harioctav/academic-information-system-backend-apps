<?php

namespace App\Http\Controllers\Api\Evaluations;

use App\Helpers\SearchHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\Evaluations\GradeRequest;
use App\Http\Resources\Academics\StudentResource;
use App\Http\Resources\Evaluations\GradeResource;
use App\Models\Grade;
use App\Models\Student;
use App\Services\Grade\GradeService;
use App\Services\Student\StudentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class GradeController extends Controller
{
  protected $gradeService, $studentService;

  /**
   * Create a new controller instance.
   *
   * @return void
   */
  public function __construct(
    StudentService $studentService,
    GradeService $gradeService,
  ) {
    $this->studentService = $studentService;
    $this->gradeService = $gradeService;
  }

  /**
   * Retrieves a query for grades with associated recommendations.
   *
   * @param  \Illuminate\Database\Eloquent\Builder|null  $query  The base query to start from, or null to use the default Grade query.
   * @return \Illuminate\Database\Eloquent\Builder  The modified query with the recommendations data.
   */
  private function getGradeQueryWithRecommendations($query = null)
  {
    $query = $query ?? Grade::query();

    return $query->select('grades.*')
      ->leftJoin('recommendations', function ($join) {
        $join->on('grades.subject_id', '=', 'recommendations.subject_id')
          ->where('recommendations.student_id', '=', DB::raw('grades.student_id'));
      })
      ->addSelect([
        'recommendations.id as recommendation_id',
        'recommendations.uuid as recommendation_uuid',
        'recommendations.recommendation_note',
        'recommendations.semester as recommendation_semester'
      ])
      ->with([
        'subject:id,uuid,code,name,course_credit',
        'student.major:id,uuid,code,name',
        'student:id,uuid,nim,name,major_id'
      ]);
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
  public function store(GradeRequest $request, Student $student)
  {
    return $this->gradeService->handleStore($request, $student);
  }

  /**
   * Display the specified resource.
   */
  public function show(Student $student, Request $request)
  {
    $query = $this->getGradeQueryWithRecommendations()
      ->where('grades.student_id', $student->id);

    if ($request->has('recommendation_note')) {
      $query->where('recommendations.recommendation_note', $request->recommendation_note);
    }

    if ($request->has('semester')) {
      $query->where('recommendations.semester', $request->semester);
    }

    $query = SearchHelper::applySearchQuery(
      query: $query,
      request: $request,
      searchableFields: [
        'grade',
        'exam_period',
      ],
      sortableFields: [
        'grade',
        'quality',
        'mutu',
        'created_at',
        'updated_at'
      ],
      filterFields: [
        'grade_note',
        'exam_period',
        'grade'
      ]
    );

    $perPage = $request->input('per_page', 5);
    $result = $query->latest();

    return GradeResource::collection(
      $perPage == -1 ? $result->get() : $result->paginate($perPage)
    );
  }

  public function detail(Grade $grade): GradeResource
  {
    $grade = $this->getGradeQueryWithRecommendations()
      ->where('grades.id', $grade->id)
      ->first();

    return new GradeResource($grade);
  }

  /**
   * Update the specified resource in storage.
   */
  public function update(GradeRequest $request, Grade $grade)
  {
    return $this->gradeService->handleUpdate($request, $grade);
  }

  /**
   * Remove the specified resource from storage.
   */
  public function destroy(Grade $grade)
  {
    return $this->gradeService->handleDelete($grade);
  }

  /**
   * Remove multiple resources from storage.
   */
  public function bulkDestroy(Request $request): JsonResponse
  {
    $request->validate([
      'ids' => 'required|array',
      'ids.*' => 'exists:grades,uuid'
    ]);

    return $this->gradeService->handleBulkDelete($request->ids);
  }

  public function export(Student $student): JsonResponse
  {
    return $this->gradeService->handleExport($student);
  }
}

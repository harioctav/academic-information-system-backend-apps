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
    $query = SearchHelper::applySearchQuery(
      query: $this->gradeService->query()
        ->select('grades.*')
        ->where('grades.student_id', $student->id)
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
          'student:id,uuid,nim,name'
        ]),
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
        'exam_period'
      ]
    );

    $perPage = $request->input('per_page', 5);
    $result = $query->latest();

    return GradeResource::collection(
      $perPage == -1 ? $result->get() : $result->paginate($perPage)
    );
  }

  /**
   * Update the specified resource in storage.
   */
  public function update(Request $request, Grade $grade)
  {
    //
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
}

<?php

namespace App\Http\Controllers\Api\Evaluations;

use App\Helpers\SearchHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\Evaluations\GradeRequest;
use App\Http\Resources\Academics\StudentResource;
use App\Models\Grade;
use App\Models\Student;
use App\Services\Grade\GradeService;
use App\Services\Student\StudentService;
use Illuminate\Http\Request;

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
  public function show(Grade $grade)
  {
    //
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
    //
  }
}

<?php

namespace App\Http\Controllers\Api\Academics;

use App\Enums\GeneralConstant;
use App\Helpers\SearchHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\Academics\StudentRequest;
use App\Http\Resources\Academics\StudentResource;
use App\Models\Student;
use App\Services\Student\StudentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class StudentController extends Controller
{
  /**
   * The Student Service instance used by this controller.
   */
  protected $studentService;

  /**
   * Create a new controller instance.
   *
   * @return void
   */
  public function __construct(
    StudentService $studentService
  ) {
    $this->studentService = $studentService;
  }

  /**
   * Display a listing of the resource.
   */
  public function index(Request $request)
  {
    $query = SearchHelper::applySearchQuery(
      query: $this->studentService->query()->with(['domicileAddress.village', 'idCardAddress.village']),
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
      relationFields: [
        'major_id',
        'gender',
        'religion',
        'status_registration'
      ],
      enumFields: [
        'status_activity' => GeneralConstant::class
      ]
    );

    return StudentResource::collection(
      $query->latest()->paginate($request->input('per_page', 5))
    );
  }


  /**
   * Store a newly created resource in storage.
   */
  public function store(StudentRequest $request): JsonResponse
  {
    return $this->studentService->handleStore($request);
  }

  /**
   * Display the specified resource.
   */
  public function show(Student $student): StudentResource
  {
    return new StudentResource($student->load([
      'domicileAddress.village',
      'idCardAddress.village'
    ]));
  }

  /**
   * Update the specified resource in storage.
   */
  public function update(StudentRequest $request, Student $student): JsonResponse
  {
    return $this->studentService->handleUpdate($request, $student);
  }

  /**
   * Remove the specified resource from storage.
   */
  public function destroy(Student $student): JsonResponse
  {
    return $this->studentService->handleDestroy($student);
  }

  /**
   * Remove multiple resources from storage.
   */
  public function bulkDestroy(Request $request)
  {
    $request->validate([
      'ids' => 'required|array',
      'ids.*' => 'exists:students,uuid'
    ]);

    return $this->studentService->handleBulkDelete($request->ids);
  }
}
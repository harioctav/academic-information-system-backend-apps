<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\Major\MajorService;
use App\Services\Student\StudentService;
use App\Services\Subject\SubjectService;
use App\Services\User\UserService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;

class HomeController extends Controller
{
  protected $userService, $majorService, $subjectService, $studentService;

  /**
   * Create a new controller instance.
   *
   * @return void
   */
  public function __construct(
    UserService $userService,
    MajorService $majorService,
    SubjectService $subjectService,
    StudentService $studentService
  ) {
    $this->userService = $userService;
    $this->majorService = $majorService;
    $this->subjectService = $subjectService;
    $this->studentService = $studentService;
  }

  /**
   * Display a listing of the resource.
   */
  public function index()
  {
    $data = [
      'users' => $this->userService->query()->active()->count(),
      'majors' => $this->majorService->query()->count(),
      'subjects' => $this->subjectService->query()->count(),
      'students' => $this->studentService->query()->count()
    ];

    return Response::json([
      'message' => "Berhasil mengambil data dashboard.",
      'data' => $data
    ], 200);
  }
}

<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\Major\MajorService;
use App\Services\Subject\SubjectService;
use App\Services\User\UserService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;

class HomeController extends Controller
{
  protected $userService, $majorService, $subjectService;

  /**
   * Create a new controller instance.
   *
   * @return void
   */
  public function __construct(
    UserService $userService,
    MajorService $majorService,
    SubjectService $subjectService,
  ) {
    $this->userService = $userService;
    $this->majorService = $majorService;
    $this->subjectService = $subjectService;
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
    ];

    return Response::json([
      'data' => $data
    ], 200);
  }
}

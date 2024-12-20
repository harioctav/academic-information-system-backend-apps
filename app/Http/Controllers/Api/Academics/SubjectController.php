<?php

namespace App\Http\Controllers\Api\Academics;

use App\Http\Controllers\Controller;
use App\Http\Requests\Academics\SubjectRequest;
use App\Models\Subject;
use App\Services\Subject\SubjectService;
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
    //
  }

  /**
   * Store a newly created resource in storage.
   */
  public function store(SubjectRequest $request)
  {
    //
  }

  /**
   * Display the specified resource.
   */
  public function show(Subject $subject)
  {
    //
  }

  /**
   * Update the specified resource in storage.
   */
  public function update(SubjectRequest $request, Subject $subject)
  {
    //
  }

  /**
   * Remove the specified resource from storage.
   */
  public function destroy(Subject $subject)
  {
    //
  }

  /**
   * Remove multiple resources from storage.
   */
  public function bulkDestroy(Request $request)
  {
    // 
  }
}

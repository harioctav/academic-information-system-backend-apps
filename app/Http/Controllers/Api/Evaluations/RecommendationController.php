<?php

namespace App\Http\Controllers\Api\Evaluations;

use App\Http\Controllers\Controller;
use App\Http\Requests\Evaluations\RecommendationRequest;
use App\Models\Recommendation;
use App\Services\Recommendation\RecommendationService;
use App\Services\Student\StudentService;
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
    //
  }

  /**
   * Store a newly created resource in storage.
   */
  public function store(RecommendationRequest $request)
  {
    //
  }

  /**
   * Display the specified resource.
   */
  public function show(Recommendation $recommendation)
  {
    //
  }

  /**
   * Update the specified resource in storage.
   */
  public function update(RecommendationRequest $request, Recommendation $recommendation)
  {
    //
  }

  /**
   * Remove the specified resource from storage.
   */
  public function destroy(Recommendation $recommendation)
  {
    //
  }
}

<?php

namespace App\Http\Controllers\Api\Academics;

use App\Helpers\SearchHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\Academics\MajorRequest;
use App\Http\Resources\Academics\MajorResource;
use App\Models\Major;
use App\Services\Major\MajorService;
use Illuminate\Http\Request;

class MajorController extends Controller
{
  /**
   * The MajorService instance used by this controller.
   */
  protected $majorService;

  /**
   * Create a new controller instance.
   *
   * @return void
   */
  public function __construct(
    MajorService $majorService
  ) {
    $this->majorService = $majorService;
  }

  /**
   * Display a listing of the resource.
   */
  public function index(Request $request)
  {
    $query = SearchHelper::applySearchQuery(
      query: $this->majorService->query(),
      request: $request,
      searchableFields: [
        'code',
        'name',
      ],
      sortableFields: [
        'name',
        'code',
        'created_at',
        'updated_at'
      ]
    );

    return MajorResource::collection(
      $query->latest()->paginate($request->input('per_page', 5))
    );
  }

  /**
   * Store a newly created resource in storage.
   */
  public function store(MajorRequest $request)
  {
    //
  }

  /**
   * Display the specified resource.
   */
  public function show(Major $major)
  {
    //
  }

  /**
   * Update the specified resource in storage.
   */
  public function update(MajorRequest $request, Major $major)
  {
    //
  }

  /**
   * Remove the specified resource from storage.
   */
  public function destroy(Major $major)
  {
    //
  }
}

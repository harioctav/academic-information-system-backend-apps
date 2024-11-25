<?php

namespace App\Http\Controllers\Api\Settings;

use App\Helpers\SearchHelper;
use App\Http\Controllers\Controller;
use App\Http\Resources\Settings\PermissionCategoryResource;
use App\Models\PermissionCategory;
use App\Services\PermissionCategory\PermissionCategoryService;
use Illuminate\Http\Request;

class PermissionCategoryController extends Controller
{
  /**
   * The instance used by this controller.
   */
  protected $permissionCategoryService;

  /**
   * Create a new controller instance.
   *
   * @return void
   */
  public function __construct(
    PermissionCategoryService $permissionCategoryService
  ) {
    $this->permissionCategoryService = $permissionCategoryService;
  }

  /**
   * Display a listing of the resource.
   */
  public function index(Request $request)
  {
    $query = SearchHelper::applySearchQuery(
      query: $this->permissionCategoryService->query(),
      request: $request,
      searchableFields: [
        'name',
      ],
      sortableFields: [
        'name',
        'created_at',
        'updated_at'
      ],
    );

    return PermissionCategoryResource::collection(
      $query->latest()->paginate($request->input('per_page', 5))
    );
  }

  /**
   * Store a newly created resource in storage.
   */
  public function store(Request $request)
  {
    //
  }

  /**
   * Display the specified resource.
   */
  public function show(PermissionCategory $permissionCategory)
  {
    //
  }

  /**
   * Update the specified resource in storage.
   */
  public function update(Request $request, PermissionCategory $permissionCategory)
  {
    //
  }

  /**
   * Remove the specified resource from storage.
   */
  public function destroy(PermissionCategory $permissionCategory)
  {
    //
  }
}

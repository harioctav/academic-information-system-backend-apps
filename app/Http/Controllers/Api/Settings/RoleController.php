<?php

namespace App\Http\Controllers\Api\Settings;

use App\Enums\UserRole;
use App\Helpers\SearchHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\Settings\RoleRequest;
use App\Http\Resources\Settings\RoleResource;
use App\Models\Role;
use App\Services\Role\RoleService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RoleController extends Controller
{
  /**
   * The RoleService instance used by this controller.
   */
  protected $roleService;

  /**
   * Create a new controller instance.
   *
   * @return void
   */
  public function __construct(
    RoleService $roleService
  ) {
    $this->roleService = $roleService;
  }

  /**
   * Display a listing of the resource.
   */
  public function index(Request $request)
  {
    $query = SearchHelper::applySearchQuery(
      query: $this->roleService->query(),
      request: $request,
      searchableFields: [
        'name',
      ],
      sortableFields: [
        'name',
        'created_at',
        'updated_at'
      ],
      enumFields: [
        'name' => UserRole::class
      ]
    );

    // Tambahkan kondisi untuk exclude super_admin jika parameter exclude_super_admin=true
    if ($request->has('exclude_super_admin') && $request->exclude_super_admin === 'true') {
      $query->where('name', '!=', UserRole::SuperAdmin->value);
    }

    return RoleResource::collection(
      $query->latest()->paginate($request->input('per_page', 5))
    );
  }

  /**
   * Store a newly created resource in storage.
   */
  public function store(RoleRequest $request)
  {
    return $this->roleService->handleStore($request);
  }

  /**
   * Display the specified resource.
   */
  public function show(Role $role): RoleResource
  {
    return new RoleResource($role);
  }

  /**
   * Update the specified resource in storage.
   */
  public function update(RoleRequest $request, Role $role): JsonResponse
  {
    return $this->roleService->handleUpdate($request, $role);
  }

  /**
   * Remove the specified resource from storage.
   */
  public function destroy(Role $role): JsonResponse
  {
    return $this->roleService->handleDelete($role);
  }

  /**
   * Remove multiple resources from storage.
   */
  public function bulkDestroy(Request $request): JsonResponse
  {
    $request->validate([
      'ids' => 'required|array',
      'ids.*' => 'exists:roles,uuid'
    ]);

    return $this->roleService->handleBulkDelete($request->ids);
  }
}

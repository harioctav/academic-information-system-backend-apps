<?php

namespace App\Http\Controllers\Api\Settings;

use App\Enums\GeneralConstant;
use App\Helpers\SearchHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\Settings\UserRequest;
use App\Http\Resources\Settings\UserResource;
use App\Models\User;
use App\Services\User\UserService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class UserController extends Controller
{
  /**
   * The UserService instance used by this controller.
   */
  protected $userService;

  /**
   * Create a new controller instance.
   *
   * @return void
   */
  public function __construct(
    UserService $userService
  ) {
    $this->userService = $userService;
  }

  /**
   * Display a listing of the resource.
   */
  public function index(Request $request)
  {
    $baseQuery = $this->userService->query()->whereNotAdmin();

    if ($request->has('roles')) {
      $baseQuery->whereHas('roles', function ($query) use ($request) {
        $query->where('name', $request->roles);
      });
    }

    $query = SearchHelper::applySearchQuery(
      query: $baseQuery,
      request: $request,
      searchableFields: [
        'name',
        'email',
        'status',
        'phone'
      ],
      sortableFields: [
        'name',
        'email',
        'created_at',
        'updated_at'
      ],
      enumFields: [
        'status' => GeneralConstant::class
      ],
      relationFields: [
        'status'
      ]
    );

    return UserResource::collection(
      $query->latest()->paginate($request->input('per_page', 5))
    );
  }

  /**
   * Store a newly created resource in storage.
   */
  public function store(UserRequest $request): JsonResponse
  {
    $result = new UserResource($this->userService->handleStore($request));

    return response()->json([
      'message' => "Successfully created User Data",
      'data' => $result,
    ]);
  }

  /**
   * Display the specified resource.
   */
  public function show(User $user): UserResource
  {
    return new UserResource($user);
  }

  /**
   * Update the specified resource in storage.
   */
  public function update(UserRequest $request, User $user): JsonResponse
  {
    $result = new UserResource(
      $this->userService->handleUpdate($request, $user)
    );

    return response()->json([
      'message' => "Successfully updated User Data",
      'data' => $result,
    ]);
  }

  /**
   * Remove the specified resource from storage.
   */
  public function destroy(User $user): JsonResponse
  {
    return $this->userService->handleDelete($user);
  }

  /**
   * Remove multiple resources from storage.
   */
  public function bulkDestroy(Request $request)
  {
    $request->validate([
      'ids' => 'required|array',
      'ids.*' => 'exists:users,uuid'
    ]);

    return $this->userService->handleBulkDelete($request->ids);
  }

  /**
   * Remove the specified image user from storage.
   */
  public function deleteImage(User $user): JsonResponse
  {
    return $this->userService->handleDeleteImage($user);
  }

  public function status(USer $user): JsonResponse
  {
    return $this->userService->handleChangeStatus($user);
  }
}

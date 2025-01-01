<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Settings\UserRequest;
use App\Http\Resources\Settings\UserResource;
use App\Models\User;
use App\Services\User\UserService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Response;

class AccountController extends Controller
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

  public function profile(UserRequest $request, User $user): JsonResponse
  {
    return $this->userService->handleUpdate($request, $user);
  }

  /**
   * Remove the specified image user from storage.
   */
  public function deleteImage(User $user): JsonResponse
  {
    return $this->userService->handleDeleteImage($user);
  }
}

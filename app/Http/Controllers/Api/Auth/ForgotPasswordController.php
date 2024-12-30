<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\ForgotPasswordRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Response;

class ForgotPasswordController extends Controller
{
  public function forgotPassword(ForgotPasswordRequest $request): JsonResponse
  {
    $status = Password::sendResetLink(
      $request->only('email')
    );

    return $status === Password::RESET_LINK_SENT
      ? Response::json(['message' => 'Reset password link sent to your email'])
      : Response::json(['errors' => ['email' => [__($status)]]], 400);
  }

  public function resetPassword(Request $request): JsonResponse
  {
    $request->validate([
      'token' => 'required',
      'email' => 'required|email',
      'password' => 'required|min:8|confirmed',
    ]);

    $status = Password::reset(
      $request->only('email', 'password', 'password_confirmation', 'token'),
      function (User $user, string $password) {
        $user->forceFill([
          'password' => Hash::make($password)
        ])->save();
      }
    );

    return $status === Password::PASSWORD_RESET
      ? Response::json(['message' => 'Password has been reset'])
      : Response::json(['errors' => ['email' => [__($status)]]], 400);
  }
}

<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Auth\LoginRequest;
use App\Http\Requests\Api\Auth\RefreshTokenRequest;
use App\Http\Resources\Settings\UserResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Response;
use Laravel\Passport\Client;

class AuthController extends Controller
{
  /**
   * Authenticate a user and return a JWT token.
   *
   * @param LoginRequest $request
   * @return JsonResponse
   */
  public function login(LoginRequest $request): JsonResponse
  {
    $remember = $request->boolean('remember', false);

    if (!Auth::attempt($request->only('email', 'password'), $remember)) {
      return Response::json([
        'errors' => [
          'email' => ['The provided credentials are incorrect.']
        ]
      ], 401);
    }

    $token = $this->getOAuthToken(
      $request->email,
      $request->password,
      $remember
    );

    $user = Auth::user();

    return Response::json([
      'message' => 'User has been logged successfully.',
      'user' => array_merge(
        (new UserResource($user))->toArray($request),
        ['token' => $token]
      )
    ]);
  }

  /**
   * Refresh an OAuth token using the provided refresh token.
   *
   * @param RefreshTokenRequest $request
   * @return JsonResponse
   */
  public function refreshToken(RefreshTokenRequest $request): JsonResponse
  {
    $token = $this->refreshOAuthToken($request->refresh_token);

    return Response::json([
      'message' => 'Refreshed token.',
      'token' => $token,
    ]);
  }

  /**
   * Get the authenticated user's information.
   *
   * @return JsonResponse
   */
  public function user(): JsonResponse
  {
    return Response::json([
      'message' => 'Authenticated user info.',
      'user' => new UserResource(Auth::user()),
    ]);
  }

  /**
   * Log the authenticated user out of the application.
   *
   * @param Request $request
   * @return JsonResponse
   */
  public function logout(Request $request): JsonResponse
  {
    // $request->user()->token()->revoke();
    $request->user()->tokens()->delete();

    return Response::json([
      'message' => 'Logged out successfully.'
    ]);
  }

  /**
   * Get an OAuth token using the provided email and password.
   *
   * @param string $email The email address to use for authentication.
   * @param string $password The password to use for authentication.
   * @return array The OAuth token response.
   */
  private function getOAuthToken(string $email, string $password, bool $remember = false): array
  {
    $response = Http::post(config('app.url') . '/oauth/token', [
      'grant_type' => 'password',
      'client_id' => config('passport.client_id'),
      'client_secret' => config('passport.client_secret'),
      'username' => $email,
      'password' => $password,
      'scope' => '',
      'remember' => $remember
    ]);

    return $response->json();
  }

  /**
   * Refresh an OAuth token using the provided refresh token.
   *
   * @param string $refreshToken The refresh token to use for refreshing the OAuth token.
   * @return array The refreshed OAuth token response.
   */
  private function refreshOAuthToken(string $refreshToken): array
  {
    $client = Client::findOrFail(config('passport.client_id'));

    $response = Http::asForm()->post(config('app.url') . '/oauth/token', [
      'grant_type' => 'refresh_token',
      'refresh_token' => $refreshToken,
      'client_id' => $client->id,
      'client_secret' => $client->secret,
      'scope' => '',
    ]);

    return $response->json();
  }
}

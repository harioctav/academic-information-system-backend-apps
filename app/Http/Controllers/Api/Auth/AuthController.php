<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Auth\LoginRequest;
use App\Http\Requests\Api\Auth\RefreshTokenRequest;
use App\Http\Resources\Settings\UserResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Response;
use Laravel\Passport\Client;

class AuthController extends Controller
{
  public function login(LoginRequest $request): JsonResponse
  {
    $remember = $request->boolean('remember', false);

    if (!Auth::attempt($request->only('email', 'password'), $remember)) {
      return Response::json([
        'errors' => [
          'email' => ['Kredensial ini tidak cocok dengan catatan kami.']
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
      'user' => new UserResource($user)
    ])->withCookie(
      'access_token',
      $token['access_token'],
      60, // 1 hour
      '/',
      null,
      true, // secure
      true, // httpOnly
      false,
      'Strict'
    )->withCookie(
      'refresh_token',
      $token['refresh_token'],
      1440, // 24 hours
      '/',
      null,
      true,
      true,
      false,
      'Strict'
    );
  }

  /**
   * Refresh an OAuth token using the provided refresh token.
   *
   * @param RefreshTokenRequest $request
   * @return JsonResponse
   */
  public function refreshToken(Request $request): JsonResponse
  {
    $refreshToken = $request->cookie('refresh_token');

    if (!$refreshToken) {
      return Response::json([
        'message' => 'Refresh token not found'
      ], 401);
    }

    try {
      $token = $this->refreshOAuthToken($refreshToken);
      $user = Auth::user();

      // Invalidate old refresh token
      DB::table('oauth_refresh_tokens')
        ->where('refresh_token', hash('sha256', $refreshToken))
        ->update(['revoked' => true]);

      return Response::json([
        'message' => 'Token refreshed successfully.',
        'user' => new UserResource($user)
      ])->withCookie(
        'access_token',
        $token['access_token'],
        60,
        '/',
        null,
        true,
        true,
        false,
        'Strict'
      )->withCookie(
        'refresh_token',
        $token['refresh_token'],
        1440,
        '/',
        null,
        true,
        true,
        false,
        'Strict'
      );
    } catch (\Exception $e) {
      return Response::json([
        'message' => 'Invalid refresh token'
      ], 401);
    }
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
    $request->user()->tokens()->delete();

    $cookie = Cookie::forget('access_token');
    $refreshCookie = Cookie::forget('refresh_token');

    return Response::json([
      'message' => 'Logged out successfully.'
    ])->withCookie($cookie)->withCookie($refreshCookie);
  }

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

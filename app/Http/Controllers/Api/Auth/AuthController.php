<?php

namespace App\Http\Controllers\Api\Auth;

use App\Enums\GeneralConstant;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RefreshTokenRequest;
use App\Http\Resources\Settings\UserResource;
use App\Models\User;
use App\Services\Security\SecurityService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Response;
use Symfony\Component\HttpFoundation\Response as HttpFoundationResponse;
use Laravel\Passport\Client;

class AuthController extends Controller
{
  /**
   * The instance used by this controller.
   */
  protected $securityService;

  /**
   * Create a new controller instance.
   *
   * @return void
   */
  public function __construct(SecurityService $securityService)
  {
    $this->securityService = $securityService;
  }

  /**
   * Authenticate a user and return a JWT token.
   *
   * @param LoginRequest $request
   * @return JsonResponse
   */
  public function login(LoginRequest $request): JsonResponse
  {
    $user = Cache::remember('user.' . $request->email, 60, function () use ($request) {
      return User::with(['roles', 'permissions'])->where('email', $request->email)->first();
    });

    if ($user && $user->status->value == GeneralConstant::InActive->value) {
      return Response::json([
        'message' => 'Login gagal.',
        'errors' => [
          'email' => [
            'Akun sedang tidak aktif. Silakan hubungi Administrator untuk mengaktifkan.'
          ]
        ]
      ], HttpFoundationResponse::HTTP_LOCKED);
    }

    if ($user && $user->isLocked()) {
      return Response::json([
        'message' => 'Login gagal.',
        'errors' => [
          'email' => [
            'Akun terkunci. Silakan coba lagi nanti.'
          ]
        ]
      ], HttpFoundationResponse::HTTP_LOCKED);
    }

    $remember = $request->boolean('remember', false);

    if (!Auth::attempt($request->only('email', 'password'), $remember)) {
      if ($user) {
        $this->securityService->handleLoginAttempt($user, $request, false);
      }

      return Response::json([
        'message' => 'Login gagal.',
        'errors' => [
          'email' => [
            'Kredensial ini tidak cocok dengan catatan kami.'
          ]
        ]
      ], HttpFoundationResponse::HTTP_UNPROCESSABLE_ENTITY);
    }

    $this->securityService->handleLoginAttempt($user, $request, true);

    $token = $this->getOAuthToken(
      $request->email,
      $request->password,
      $remember
    );

    return Response::json([
      'message' => 'Berhasil masuk ke dalam sistem.',
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
    $user = Auth::user();
    $user->update(['last_activity' => now()]);

    return Response::json([
      'message' => 'Authenticated user info.',
      'user' => new UserResource($user),
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
    $user = $request->user();
    $user->update(['last_activity' => null]);
    $user->tokens()->delete();

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
    try {
      // Gunakan URL absolut internal (127.0.0.1 atau alamat container) 
      // untuk mencegah masalah SSL dan routing
      $response = Http::withoutVerifying()->post(config('app.url') . '/oauth/token', [
        'grant_type' => 'password',
        'client_id' => config('passport.client_id'),
        'client_secret' => config('passport.client_secret'),
        'username' => $email,
        'password' => $password,
        'scope' => '',
      ]);

      if (!$response->successful()) {
        Log::error('OAuth token error', [
          'status' => $response->status(),
          'body' => $response->body()
        ]);

        return [
          'error' => 'oauth_error',
          'message' => $response->json()['message'] ?? 'Failed to get OAuth token',
          'status' => $response->status()
        ];
      }

      return $response->json();
    } catch (\Exception $e) {
      Log::error('OAuth exception', [
        'message' => $e->getMessage(),
        'trace' => $e->getTraceAsString()
      ]);

      return [
        'error' => 'oauth_exception',
        'message' => 'Failed to get OAuth token: ' . $e->getMessage(),
        'status' => 500
      ];
    }
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

    try {
      $response = Http::withoutVerifying()->post(config('app.url') . '/oauth/token', [
        'grant_type' => 'refresh_token',
        'refresh_token' => $refreshToken,
        'client_id' => $client->id,
        'client_secret' => $client->secret,
        'scope' => '',
      ]);

      if (!$response->successful()) {
        Log::error('OAuth token error', [
          'status' => $response->status(),
          'body' => $response->body()
        ]);

        return [
          'error' => 'oauth_error',
          'message' => $response->json()['message'] ?? 'Failed to get OAuth token',
          'status' => $response->status()
        ];
      }

      return $response->json();
    } catch (\Exception $e) {
      Log::error('OAuth exception', [
        'message' => $e->getMessage(),
        'trace' => $e->getTraceAsString()
      ]);

      return [
        'error' => 'oauth_exception',
        'message' => 'Failed to get OAuth token: ' . $e->getMessage(),
        'status' => 500
      ];
    }
  }
}

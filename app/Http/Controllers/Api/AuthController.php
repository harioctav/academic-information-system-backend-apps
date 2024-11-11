<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RefreshTokenRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Response;
use Laravel\Passport\Client;

class AuthController extends Controller
{
  public function login(LoginRequest $request): JsonResponse
  {
    if (Auth::attempt(['email' => $request->email, 'password' => $request->password])) {
      $user = Auth::user();

      $response = Http::post(env('APP_URL') . '/oauth/token', [
        'grant_type' => 'password',
        'client_id' => env('PASSPORT_CLIENT_ID'),
        'client_secret' => env('PASSPORT_CLIENT_SECRET'),
        'username' => $request->email,
        'password' => $request->password,
        'scope' => ''
      ]);

      $user['token'] = $response->json();

      return Response::json(
        data: [
          'message' => 'User has been logged successfully.',
          'user' => $user,
        ],
        status: 200
      );
    } else {
      return Response::json(
        data: [
          'errors' => [
            'email' => ['The provided credentials are incorrect.']
          ]
        ],
        status: 401
      );
    }
  }

  public function refreshToken(RefreshTokenRequest $request): JsonResponse
  {
    $client = Client::where('password_client', 1)->first();

    $response = Http::asForm()->post(env('APP_URL') . '/oauth/token', [
      'grant_type' => 'refresh_token',
      'refresh_token' => $request->refresh_token,
      'client_id' => $client->id,
      'client_secret' => $client->secret,
      'scope' => '',
    ]);

    return Response::json([
      'message' => 'Refreshed token.',
      'token' => $response->json(),
    ]);
  }

  public function user(): JsonResponse
  {
    $user = Auth::user();
    return Response::json([
      'message' => 'Authenticated use info.',
      'user' => $user,
    ], 200);
  }

  public function logout(Request $request): JsonResponse
  {
    $request->user()->token()->revoke();
    return Response::json([
      'message' => 'Logged out successfully.'
    ]);
  }
}

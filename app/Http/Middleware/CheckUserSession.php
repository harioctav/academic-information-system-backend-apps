<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckUserSession
{
  /**
   * Handle an incoming request.
   *
   * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
   */
  public function handle(Request $request, Closure $next): Response
  {
    $user = $request->user();

    if (
      !$user->last_activity ||
      $user->last_activity->diffInMinutes(now()) > config('auth.session_lifetime', 120)
    ) {
      $user->tokens()->delete();
      return response()->json(['message' => 'Session expired'], 401);
    }

    $user->last_activity = now();
    $user->save();

    return $next($request);
  }
}

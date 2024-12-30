<?php

namespace App\Http\Middleware;

use App\Enums\GeneralConstant;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class InActiveUser
{
  /**
   * Handle an incoming request.
   *
   * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
   */
  public function handle(Request $request, Closure $next): Response
  {
    if (Auth::check() && Auth::user()->status->value == GeneralConstant::InActive->value) {
      Auth::logout();

      return response()->json([
        'success' => false,
        'message' => trans('session.banned'),
        'data' => null
      ], Response::HTTP_FORBIDDEN);
    }

    return $next($request);
  }
}

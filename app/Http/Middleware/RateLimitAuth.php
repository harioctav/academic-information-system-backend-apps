<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Cache\RateLimiter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RateLimitAuth
{
  protected $limiter;

  public function __construct(RateLimiter $limiter)
  {
    $this->limiter = $limiter;
  }

  /**
   * Handle an incoming request.
   *
   * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
   */
  public function handle(Request $request, Closure $next): Response
  {
    $key = 'auth_attempt_' . $request->ip();

    // Max 5 attempts per minute
    if ($this->limiter->tooManyAttempts($key, 5)) {
      $seconds = $this->limiter->availableIn($key);

      return new JsonResponse([
        'message' => 'Too many attempts',
        'try_after_seconds' => $seconds
      ], 429);
    }

    $this->limiter->hit($key, 60); // Keep record for 60 seconds

    $response = $next($request);

    // If authentication successful, clear the rate limit
    if ($response->getStatusCode() === 200) {
      $this->limiter->clear($key);
    }

    return $response;
  }
}

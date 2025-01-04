<?php

namespace App\Services\Security;

use App\Jobs\Auth\ProcessLoginActivity;
use LaravelEasyRepository\ServiceApi;
use App\Repositories\Security\SecurityRepository;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Queue;

class SecurityServiceImplement extends ServiceApi implements SecurityService
{

  /**
   * set title message api for CRUD
   * @param string $title
   */
  protected string $title = "";
  /**
   * uncomment this to override the default message
   * protected string $create_message = "";
   * protected string $update_message = "";
   * protected string $delete_message = "";
   */

  /**
   * don't change $this->mainRepository variable name
   * because used in extends service class
   */
  protected SecurityRepository $mainRepository;

  public function __construct(
    SecurityRepository $mainRepository
  ) {
    $this->mainRepository = $mainRepository;
  }

  public function query()
  {
    return $this->mainRepository->query();
  }

  public function getWhere(
    $wheres = [],
    $columns = '*',
    $comparisons = '=',
    $orderBy = null,
    $orderByType = null
  ) {
    return $this->mainRepository->getWhere(
      wheres: $wheres,
      columns: $columns,
      comparisons: $comparisons,
      orderBy: $orderBy,
      orderByType: $orderByType
    );
  }

  private function getLocation(string $ip): ?string
  {
    return Cache::remember('ip_location_' . $ip, 60 * 24, function () use ($ip) {
      // Implement IP geolocation logic here
      return null;
    });
  }

  public function handleLoginAttempt(\App\Models\User $user, \Illuminate\Http\Request $request, bool $success)
  {
    // Dispatch job instead of using Queue facade directly
    ProcessLoginActivity::dispatch([
      'user_id' => $user->id,
      'ip_address' => $request->ip(),
      'user_agent' => $request->userAgent(),
      'location' => $this->getLocation($request->ip()),
      'status' => $success ? 'success' : 'failed',
      'login_at' => now()
    ]);

    // Cache failed login attempts
    $attemptsKey = 'login_attempts_' . $user->id;

    if (!$success) {
      $attempts = Cache::get($attemptsKey, 0) + 1;
      Cache::put($attemptsKey, $attempts, 60 * 30);

      if ($attempts >= 5) {
        $user->locked_until = now()->addMinutes(30);
        $user->save();
      }

      $user->increment('failed_login_attempts');
    } else {
      Cache::forget($attemptsKey);
      $user->failed_login_attempts = 0;
      $user->locked_until = null;
      $user->last_activity = now();
      $user->save();
    }
  }
}

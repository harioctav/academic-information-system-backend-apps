<?php

namespace App\Services\Security;

use LaravelEasyRepository\ServiceApi;
use App\Repositories\Security\SecurityRepository;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

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
    // For local testing
    if (app()->environment('local')) {
      $testIps = [
        '8.8.8.8' => 'Mountain View, California, United States',
        '1.1.1.1' => 'Sydney, New South Wales, Australia',
        '208.67.222.222' => 'San Francisco, California, United States'
      ];

      return $testIps[array_rand($testIps)];
    }

    // Production code
    if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) === false) {
      return 'Internal Network';
    }

    return Cache::remember('ip_location_' . $ip, 60 * 24, function () use ($ip) {
      try {
        $response = Http::get("https://ipwhois.app/json/{$ip}");
        $data = $response->json();

        if ($response->successful() && isset($data['city'])) {
          return "{$data['city']}, {$data['region']}, {$data['country']}";
        }

        return 'Unknown Location';
      } catch (\Exception $e) {
        Log::error('IP Geolocation error: ' . $e->getMessage());
        return 'Location Lookup Failed';
      }
    });
  }

  public function handleLoginAttempt(\App\Models\User $user, \Illuminate\Http\Request $request, bool $success)
  {
    $location = $this->getLocation($request->ip());

    // Direct database insert instead of queue for critical login data
    $this->mainRepository->create([
      'user_id' => $user->id,
      'ip_address' => $request->ip(),
      'user_agent' => $request->userAgent(),
      'location' => $location,
      'status' => $success ? 'success' : 'failed',
      'login_at' => now()
    ]);

    // Use cache for failed attempts tracking
    if (!$success) {
      Cache::increment('login_attempts_' . $user->id);
      $attempts = Cache::get('login_attempts_' . $user->id, 1);

      if ($attempts >= 5) {
        $user->locked_until = now()->addMinutes(30);
        $user->failed_login_attempts = $attempts;
        $user->save();
      }
    } else {
      Cache::forget('login_attempts_' . $user->id);
      $user->update([
        'failed_login_attempts' => 0,
        'locked_until' => null,
        'last_activity' => now()
      ]);
    }
  }
}

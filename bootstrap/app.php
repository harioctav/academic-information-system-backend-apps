<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Console\Scheduling\Schedule;

return Application::configure(basePath: dirname(__DIR__))
  ->withRouting(
    web: __DIR__ . '/../routes/web.php',
    api: __DIR__ . '/../routes/api.php',
    commands: __DIR__ . '/../routes/console.php',
    health: '/up',
  )
  ->withMiddleware(function (Middleware $middleware) {
    $middleware->alias([
      'role' => \Spatie\Permission\Middleware\RoleMiddleware::class,
      'permission' => \App\Http\Middleware\Permission::class,
      'role_or_permission' => \Spatie\Permission\Middleware\RoleOrPermissionMiddleware::class,
      'session.check' => \App\Http\Middleware\CheckUserSession::class,
      'auth.rate' => \App\Http\Middleware\RateLimitAuth::class,
      'is.in-active.user' => \App\Http\Middleware\InActiveUser::class
    ]);

    $middleware->append(\Illuminate\Http\Middleware\HandleCors::class);
  })
  ->withExceptions(function (Exceptions $exceptions) {
    //
  })
  ->withSchedule(function (Schedule $schedule) {
    $schedule->command('transcripts:cleanup')->hourly();
  })->create();

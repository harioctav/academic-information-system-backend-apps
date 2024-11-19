<?php

use App\Http\Controllers\Api\Auth\AuthController;
use App\Http\Controllers\Api\Locations\ProvinceController;
use App\Http\Controllers\Api\Locations\RegencyController;
use Illuminate\Support\Facades\Route;

Route::prefix('auth')
  ->controller(AuthController::class)
  ->group(function () {
    Route::post('login', 'login');
    Route::post('refresh', 'refreshToken');

    Route::group(['middleware' => ['auth:api']], function () {
      Route::get('user', 'user');
      Route::post('logout', 'logout');
    });
  });

Route::group(['middleware' => ['auth:api', 'permission']], function () {
  Route::prefix('locations')->group(function () {
    // Province
    Route::prefix('provinces')
      ->name('provinces.')
      ->group(function () {
        Route::delete('bulk-delete', [ProvinceController::class, 'bulkDestroy'])->name('bulk');
      });
    Route::apiResource('provinces', ProvinceController::class);

    // Regency
    Route::prefix('regencies')
      ->name('regencies.')
      ->group(function () {
        Route::delete('bulk-delete', [RegencyController::class, 'bulkDestroy'])->name('bulk');
      });
    Route::apiResource('regencies', RegencyController::class);
  });
});

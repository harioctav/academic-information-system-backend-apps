<?php

use App\Http\Controllers\Api\Auth\AuthController;
use App\Http\Controllers\Api\Locations\DistrictController;
use App\Http\Controllers\Api\Locations\ProvinceController;
use App\Http\Controllers\Api\Locations\RegencyController;
use App\Http\Controllers\Api\Locations\VillageController;
use App\Http\Controllers\Api\Settings\PermissionCategoryController;
use App\Http\Controllers\Api\Settings\RoleController;
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

    // District
    Route::prefix('districts')
      ->name('districts.')
      ->group(function () {
        Route::delete('bulk-delete', [DistrictController::class, 'bulkDestroy'])->name('bulk');
      });
    Route::apiResource('districts', DistrictController::class);

    // Village
    Route::prefix('villages')
      ->name('villages.')
      ->group(function () {
        Route::delete('bulk-delete', [VillageController::class, 'bulkDestroy'])->name('bulk');
      });
    Route::apiResource('villages', VillageController::class);
  });

  Route::prefix('settings')->group(function () {
    // Role Management
    Route::prefix('roles')
      ->name('roles')
      ->group(function () {
        Route::delete('bulk-delete', [RoleController::class, 'bulkDestroy'])->name('bulk');
      });
    Route::apiResource('roles', RoleController::class)->except('store');

    // Permission Categories
    Route::apiResource('permission-categories', PermissionCategoryController::class)->parameters([
      'permission-categories' => 'permissionCategory'
    ])->only('index');
  });
});

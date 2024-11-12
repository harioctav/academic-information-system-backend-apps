<?php

use App\Http\Controllers\Api\Auth\AuthController;
use App\Http\Controllers\Api\Locations\ProvinceController;
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

Route::group(['middleware' => ['auth:api']], function () {
  // Provinces
  Route::prefix('locations')->group(function () {
    Route::apiResource('provinces', ProvinceController::class);
  });
});

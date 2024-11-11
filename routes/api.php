<?php

use App\Http\Controllers\Api\AuthController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
  return $request->user();
})->middleware('auth:api');

Route::prefix('auth')
  ->controller(AuthController::class)
  ->group(function () {
    Route::post('login', 'login');
    Route::post('refresh', 'refreshToken');
  });

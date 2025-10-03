<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
  return response()->json([
    'name' => 'Academic Information System Universitas Terbuka Sukabumi API',
    'version' => '1.0.0',
    'status' => 'active',
    'environment' => app()->environment(),
    'timestamp' => now()->toISOString(),
    'endpoints' => [
      'health' => '/up'
    ],
  ], 200, [], JSON_PRETTY_PRINT);
});

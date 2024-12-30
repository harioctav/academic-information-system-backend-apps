<?php

use App\Http\Controllers\Api\Academics\MajorController;
use App\Http\Controllers\Api\Academics\MajorSubjectController;
use App\Http\Controllers\Api\Locations\DistrictController;
use App\Http\Controllers\Api\Locations\ProvinceController;
use App\Http\Controllers\Api\Locations\RegencyController;
use App\Http\Controllers\Api\Locations\VillageController;
use App\Http\Controllers\Api\Options\SelectRegionController;
use App\Http\Controllers\Api\Settings\RoleController;
use Illuminate\Support\Facades\Route;

Route::prefix('options')->name('options.')->group(function () {
  // Basic Options
  Route::get('roles', [RoleController::class, 'index'])->name('roles');

  // Location Options
  Route::prefix('locations')
    ->name('locations.')
    ->group(function () {
      /** Province */
      Route::controller(ProvinceController::class)
        ->name('provinces.')
        ->group(function () {
          Route::get('provinces', 'index')->name('index');
          Route::get('provinces/{province}', 'show')->name('show');
        });

      /** Regencies */
      Route::controller(RegencyController::class)
        ->name('regencies.')
        ->group(function () {
          Route::get('regencies', 'index')->name('index');
          Route::get('regencies/{regency}', 'show')->name('show');
        });

      /** Districts */
      Route::controller(DistrictController::class)
        ->name('districts.')
        ->group(function () {
          Route::get('districts', 'index')->name('index');
          Route::get('districts/{district}', 'show')->name('show');
        });

      /** Villages */
      Route::controller(VillageController::class)
        ->name('villages.')
        ->group(function () {
          Route::get('villages', 'index')->name('index');
          Route::get('villages/{village}', 'show')->name('show');
        });
    });

  // Academic Options
  Route::prefix('academics')->name('academics.')->group(function () {
    Route::get('majors', [MajorController::class, 'index'])->name('majors.index');
    Route::get('majors/{major}', [MajorController::class, 'show'])->name('majors.show');
    Route::get('majors/{major}/subjects/conditions', [MajorSubjectController::class, 'condition'])
      ->name('subjects.condition');
    Route::get('majors/{major}/subjects/{majorSubject}', [MajorSubjectController::class, 'show'])
      ->name('majors.subjects.show');
  });

  // Select Dropdowns
  Route::prefix('selects')->name('selects.')->group(function () {
    Route::controller(SelectRegionController::class)->group(function () {
      Route::get('provinces', 'provinces')->name('provinces');
      Route::get('regencies', 'regencies')->name('regencies');
      Route::get('districts', 'districts')->name('districts');
      Route::get('villages', 'villages')->name('villages');
    });
  });
});

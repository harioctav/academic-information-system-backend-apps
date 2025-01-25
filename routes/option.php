<?php

use App\Http\Controllers\Api\Academics\MajorController;
use App\Http\Controllers\Api\Academics\MajorSubjectController;
use App\Http\Controllers\Api\Academics\StudentController;
use App\Http\Controllers\Api\Academics\SubjectController;
use App\Http\Controllers\Api\Locations\DistrictController;
use App\Http\Controllers\Api\Locations\ProvinceController;
use App\Http\Controllers\Api\Locations\RegencyController;
use App\Http\Controllers\Api\Locations\VillageController;
use App\Http\Controllers\Api\Options\SelectRegionController;
use App\Http\Controllers\Api\Settings\RoleController;
use App\Http\Controllers\Api\Settings\UserController;
use Illuminate\Support\Facades\Route;

Route::prefix('options')->name('options.')->group(function () {
  // Basic Options
  Route::get('roles', [RoleController::class, 'index'])->name('roles');

  // Setting Options
  Route::prefix('settings')
    ->name('settings.')
    ->group(function () {
      /** Roles */
      Route::controller(RoleController::class)
        ->name('roles.')
        ->group(function () {
          Route::get('roles', 'index')->name('index');
          Route::get('roles/{role}', 'show')->name('show');
        });

      /** Users */
      Route::controller(UserController::class)
        ->name('users.')
        ->group(function () {
          Route::get('users', 'index')->name('index');
          Route::get('users/{user}', 'show')->name('show');
        });
    });

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
  Route::prefix('academics')
    ->name('academics.')
    ->group(function () {
      /** Majors */
      Route::controller(MajorController::class)
        ->name('majors.')
        ->group(function () {
          Route::get('majors', 'index')->name('index');
          Route::get('majors/{major}', 'show')->name('show');
        });

      /** Major Subjects */
      Route::controller(MajorSubjectController::class)
        ->name('majors.subjects.')
        ->group(function () {
          Route::get('majors/{major}/subjects/conditions', 'condition')->name('condition');
          Route::get('majors/{major}/subjects/{majorSubject}', 'show')->name('show');
        });

      /** Subjects */
      Route::controller(SubjectController::class)
        ->name('subjects.')
        ->group(function () {
          Route::get('subjects', 'index')->name('index');
          Route::get('subjects/{student}/recommendations', 'subjectListRecommendations')->name('recommendations');
          Route::get('subjects/{student}/grades', 'subjectListGrades')->name('grades');
          Route::get('subjects/{subject}', 'show')->name('show');
        });

      /** Students */
      Route::controller(StudentController::class)
        ->name('students.')
        ->group(function () {
          Route::get('students', 'index')->name('index');
          Route::get('students/{student}', 'show')->name('show');
          Route::get('students/{student}/info', 'info')->name('info');
        });
    });

  // Select Dropdowns
  Route::prefix('selects')
    ->name('selects.')
    ->group(function () {
      Route::controller(SelectRegionController::class)->group(function () {
        Route::get('provinces', 'provinces')->name('provinces');
        Route::get('regencies', 'regencies')->name('regencies');
        Route::get('districts', 'districts')->name('districts');
        Route::get('villages', 'villages')->name('villages');

        // Show spesific data
        Route::get('village/{id}', 'village')->name('village');
      });
    });
});

<?php

use App\Http\Controllers\Api\Academics\MajorSubjectController;
use App\Http\Controllers\Api\Locations\DistrictController;
use App\Http\Controllers\Api\Locations\ProvinceController;
use App\Http\Controllers\Api\Locations\RegencyController;
use App\Http\Controllers\Api\Locations\VillageController;
use App\Http\Controllers\Api\Settings\RoleController;
use Illuminate\Support\Facades\Route;

// Roles options
Route::prefix('options')
  ->name('options.')
  ->group(function () {
    Route::get('roles', [RoleController::class, 'index'])->name('roles');
    Route::get('provinces', [ProvinceController::class, 'index'])->name('provinces');
    Route::get('regencies', [RegencyController::class, 'index'])->name('regencies');
    Route::get('districts', [DistrictController::class, 'index'])->name('districts');
    Route::get('villages', [VillageController::class, 'index'])->name('villages');
    Route::get('majors/{major}/subjects/conditions', [MajorSubjectController::class, 'condition'])->name('subjects.condition');
    Route::get('majors/{major}/subjects/{majorSubject}', [MajorSubjectController::class, 'show'])->name('majors.subjects.show');
  });

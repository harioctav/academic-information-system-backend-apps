<?php

use App\Http\Controllers\Api\Academics\MajorController;
use App\Http\Controllers\Api\Academics\MajorSubjectController;
use App\Http\Controllers\Api\Academics\StudentController;
use App\Http\Controllers\Api\Academics\SubjectController;
use App\Http\Controllers\Api\Auth\AccountController;
use App\Http\Controllers\Api\Auth\AuthController;
use App\Http\Controllers\Api\Auth\ForgotPasswordController;
use App\Http\Controllers\Api\Evaluations\RecommendationController;
use App\Http\Controllers\Api\HomeController;
use App\Http\Controllers\Api\Locations\DistrictController;
use App\Http\Controllers\Api\Locations\ProvinceController;
use App\Http\Controllers\Api\Locations\RegencyController;
use App\Http\Controllers\Api\Locations\VillageController;
use App\Http\Controllers\Api\Settings\PermissionCategoryController;
use App\Http\Controllers\Api\Settings\RoleController;
use App\Http\Controllers\Api\Settings\UserController;
use App\Http\Controllers\NotificationController;
use Illuminate\Support\Facades\Route;

// Auth routes with rate limiting
Route::prefix('auth')
  ->controller(AuthController::class)
  ->middleware('auth.rate')
  ->group(function () {
    Route::post('login', 'login')->middleware('throttle:6,1');
    Route::post('refresh', 'refreshToken');

    Route::middleware([
      'auth:api',
      'session.check',
      'is.in-active.user'
    ])->group(function () {
      Route::get('user', 'user');
      Route::post('logout', 'logout');
    });

    Route::controller(ForgotPasswordController::class)
      ->group(function () {
        Route::post('forgot-password', 'forgotPassword')->name('password.email');
        Route::post('reset-password', 'resetPassword')->name('password.reset');
      });
  });

Route::middleware([
  'auth:api',
  'session.check'
])->group(function () {
  // Dashboard
  Route::prefix('home')
    ->name('home')
    ->controller(HomeController::class)
    ->group(function () {
      Route::get('', 'index');
    });

  require __DIR__ . '/option.php';

  // Accounts
  Route::prefix('accounts')
    ->controller(AccountController::class)
    ->group(function () {
      Route::get('users/{user}/delete-image', 'deleteImage');
      Route::patch('change-profile/{user}', 'profile');
      Route::post('change-password', 'changePassword');
    });

  // delete user image
  Route::prefix('settings')
    ->controller(UserController::class)
    ->group(function () {
      Route::get('users/{user}/delete-image', 'deleteImage');
    });

  // Delete student image
  Route::prefix('academics')
    ->controller(StudentController::class)
    ->group(function () {
      Route::get('students/{student}/delete-image', 'deleteImage');
    });
});

// Protected routes with enhanced security
Route::middleware([
  'auth:api',
  'permission',
  'session.check',
  'is.in-active.user'
])->group(function () {

  // Notifications
  Route::controller(NotificationController::class)
    ->group(function () {
      Route::get('notifications', 'index');
      Route::post('notifications/{id}/read', 'markAsRead');
      Route::delete('notifications/{id}', 'destroy');
    });

  // Locations routes
  Route::prefix('locations')->group(function () {
    // Province routes
    Route::prefix('provinces')
      ->name('provinces.')
      ->controller(ProvinceController::class)
      ->group(function () {
        Route::delete('bulk-delete', 'bulkDestroy')->name('bulk');
      });
    Route::apiResource('provinces', ProvinceController::class);

    // Your existing location routes remain the same
    Route::prefix('regencies')
      ->name('regencies.')
      ->controller(RegencyController::class)
      ->group(function () {
        Route::delete('bulk-delete', 'bulkDestroy')->name('bulk');
      });
    Route::apiResource('regencies', RegencyController::class);

    Route::prefix('districts')
      ->name('districts.')
      ->controller(DistrictController::class)
      ->group(function () {
        Route::delete('bulk-delete', 'bulkDestroy')->name('bulk');
      });
    Route::apiResource('districts', DistrictController::class);

    Route::prefix('villages')
      ->name('villages.')
      ->controller(VillageController::class)
      ->group(function () {
        Route::delete('bulk-delete', 'bulkDestroy')->name('bulk');
      });
    Route::apiResource('villages', VillageController::class);
  });

  // Settings routes
  Route::prefix('settings')->group(function () {
    // Roles
    Route::prefix('roles')
      ->name('roles.')
      ->controller(RoleController::class)
      ->group(function () {
        Route::delete('bulk-delete', 'bulkDestroy')->name('bulk');
      });
    Route::apiResource('roles', RoleController::class)->except('store');

    // Permissions
    Route::apiResource('permission-categories', PermissionCategoryController::class)
      ->parameters(['permission-categories' => 'permissionCategory'])
      ->only('index');

    // Users
    Route::prefix('users')
      ->name('users.')
      ->controller(UserController::class)
      ->group(function () {
        Route::put('status/{user}', 'status')->name('status');
        Route::delete('bulk-delete', 'bulkDestroy')->name('bulk');
      });
    Route::apiResource('users', UserController::class);
  });

  // Academic Resources
  Route::prefix('academics')
    ->group(function () {
      // Majors
      Route::prefix('majors')
        ->name('majors.')
        ->controller(MajorController::class)
        ->group(function () {
          Route::delete('bulk-delete', 'bulkDestroy')->name('bulk');
        });
      Route::apiResource('majors', MajorController::class);

      // Major Subjects
      Route::prefix('majors/{major}/subjects')
        ->name('majors.subjects.')
        ->controller(MajorSubjectController::class)
        ->group(function () {
          Route::delete('bulk-delete', 'bulkDestroy')->name('bulk');
          Route::get('', 'index')->name('index');
          Route::post('', 'store')->name('store');
          Route::get('{majorSubject}', 'show')->name('show');
          Route::put('{majorSubject}', 'update')->name('update');
          Route::delete('{majorSubject}', 'destroy')->name('destroy');
        });

      // Subjects
      Route::prefix('subjects')
        ->name('subjects.')
        ->controller(SubjectController::class)
        ->group(function () {
          Route::delete('bulk-delete', 'bulkDestroy')->name('bulk');
        });
      Route::apiResource('subjects', SubjectController::class);

      // Students
      Route::prefix('students')
        ->name('students.')
        ->controller(StudentController::class)
        ->group(function () {
          Route::delete('bulk-delete', 'bulkDestroy')->name('bulk');
        });
      Route::apiResource('students', StudentController::class);
    });

  // Evaluations Resources
  Route::prefix('evaluations')
    ->group(function () {

      // Recommendation Resources
      Route::prefix('recommendations')
        ->name('recommendations.')
        ->controller(RecommendationController::class)
        ->group(function () {
          // Custom routes with student parameter
          Route::delete('bulk-delete', 'bulkDestroy')->name('bulk');
          Route::post('{student}', 'store')->name('store');
          Route::get('{student}', 'show')->name('show');
        });
      Route::apiResource('recommendations', RecommendationController::class)->except(
        'store',
        'show'
      );
    });
});

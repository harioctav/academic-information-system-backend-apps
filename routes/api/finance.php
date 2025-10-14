<?php

use App\Http\Controllers\Api\Academics\StudentController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\Finances\RegistrationController;
use App\Http\Controllers\Api\Finances\InvoiceController;
use App\Http\Controllers\Api\Finances\PaymentController;
use App\Http\Controllers\Api\Finances\RegistrationBatchController;
use App\Models\Payment;


// untuk mengecek batch & student  dan submit registration
Route::prefix('registrations')->name('public.registrations.')->group(function () {
  Route::get('batch/{uuid}', [RegistrationController::class, 'showBatch'])->name('batch');
  Route::get('student/{nim}', [RegistrationController::class, 'showStudent'])->name('student');
  // Route::post('/submit', [RegistrationController::class, 'submit'])->name('submit');
});

// Route::prefix('registrations')
//   ->name('public.registrations.')
//   ->controller(RegistrationController::class)
//   ->group(function () {
//     Route::get('batch/active', 'active')->name('active-batch');
//   });

Route::prefix('public')
  ->group(function () {
    // Registrations Batches
    Route::prefix('registrations-batches')
      ->name('public.registrations-batches.')
      ->controller(RegistrationBatchController::class)
      ->group(function () {
        Route::get('active', 'active')->name('active');
        Route::get('active/{registrationBatch}', 'activeDetail')->name('active.detail');
      });

    // Registrations
    Route::prefix('registrations')
      ->name('public.registrations.')
      ->controller(RegistrationController::class)
      ->group(function () {
        Route::post('submit', 'submit')->name('submit');
      });

    // Students
    Route::prefix('students')
      ->name('public.students.')
      ->controller(StudentController::class)
      ->group(function () {
        Route::get('nim/{nim}', 'getByNim')->name('get.by.nim');
      });
  });



// menampilkan daftar tagihan by billing & student
Route::prefix('invoices')->name('public.invoices.')->group(function () {
  Route::get('billing/{uuid}', [InvoiceController::class, 'showByBilling'])->name('billing');
  Route::get('student/{nim}', [InvoiceController::class, 'showByNim'])->name('student');
});

// untuk submit payment
Route::prefix('payments')->name('public.payments.')->group(function () {
  Route::post('submit', [PaymentController::class, 'submit'])->name('submit');
});

<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\Finances\RegistrationController;
use App\Http\Controllers\Api\Finances\InvoiceController;
use App\Http\Controllers\Api\Finances\PaymentController;
use App\Models\Payment;


// untuk mengecek batch & student  dan submit registration
Route::prefix('registrations')->name('public.registrations.')->group(function () {
  Route::get('batch/{uuid}', [RegistrationController::class, 'showBatch'])->name('batch');
  Route::get('student/{nim}', [RegistrationController::class, 'showStudent'])->name('student');
  Route::post('/submit', [RegistrationController::class, 'submit'])->name('submit');
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
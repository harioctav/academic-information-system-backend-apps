<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\Finances\RegistrationController;
use App\Http\Controllers\Api\Finances\RegistrationBatchController;
use App\Http\Controllers\Api\Finances\BillingController;
use App\Http\Controllers\Api\Finances\InvoiceController;
use App\Http\Controllers\Api\Finances\PaymentController;
use App\Models\Payment;


// Public route untuk cek UUID (tanpa auth)
Route::prefix('finances/registrations')
    ->name('public.registrations.')
    ->controller(RegistrationController::class)
    ->group(function () {
        // Cek apakah batch dengan UUID masih aktif
        Route::get('check-batch/{uuid}', 'checkRegistrationBatch')
            ->name('checkBatch');

        // Ambil data mahasiswa berdasarkan NIM
        Route::get('student/{nim}', 'getMahasiswaByNim')
            ->name('getMahasiswaByNim');

        // Kirim registrasi dari mahasiswa
        Route::post('{uuid}', 'postRegistration')
            ->name('submitRegistration');
    });

Route::prefix('finances/payments')
    ->name('public.payments.')
    ->controller(PaymentController::class)
    ->group(function () {

        Route::get('student/{nim}', 'getMahasiswaByNim')
            ->name('getMahasiswaByNim');

        Route::post('submit', 'postRegistration')
            ->name('submitRegistration');
    });

Route::prefix('finances')->middleware([
    // 'auth:api',
    // 'permission',
    // 'session.check',
    // 'is.in-active.user'
])->group(function () {

    // Batch Registrations
    Route::prefix('registration-batches')
        ->name('registration-batches.')
        ->controller(RegistrationBatchController::class)
        ->group(function () {
            Route::get('/', 'index')->name('index');
            Route::post('/', 'store')->name('store');
            Route::get('{registration_batch}', 'show')->name('show');
            Route::put('{registration_batch}', 'update')->name('update');
            Route::delete('{registration_batch}', 'destroy')->name('destroy');
        });



    // Registrations
    Route::prefix('registrations')
        ->name('registrations.')
        ->controller(RegistrationController::class)
        ->group(function () {
            Route::get('/', 'index')->name('index');
            Route::post('/', 'store')->name('store');
            Route::get('{registration}', 'show')->name('show');
            Route::put('{registration}', 'update')->name('update');
            Route::delete('{registration}', 'destroy')->name('destroy');
        });

    // Billings
    Route::prefix('billings')
        ->name('billings.')
        ->controller(BillingController::class)
        ->group(function () {
            Route::get('/', 'index')->name('index');
            Route::post('/', 'store')->name('store');
            Route::get('{billing}', 'show')->name('show');
            Route::put('{billing}', 'update')->name('update');
            Route::delete('{billing}', 'destroy')->name('destroy');
        });


    // Invoices
    Route::prefix('invoices')
        ->name('invoices.')
        ->controller(InvoiceController::class)
        ->group(function () {
            Route::get('/', 'index')->name('index');
            Route::post('/', 'store')->name('store');
            Route::get('{id}', 'show')->name('show');
            Route::put('{id}', 'update')->name('update');
            Route::delete('{id}', 'destroy')->name('destroy');
        });



    // Payments
    Route::prefix('payments')
        ->name('payments.')
        ->controller(PaymentController::class)
        ->group(function () {
            Route::get('/', 'index')->name('index');
            Route::post('/', 'store')->name('store');
            Route::get('{id}', 'show')->name('show');
            Route::put('{id}', 'update')->name('update');
            Route::delete('{id}', 'destroy')->name('destroy');
        });
});

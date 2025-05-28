<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\Finances\RegistrationController;
use App\Http\Controllers\Api\Finances\RegistrationBatchController;
use App\Http\Controllers\Api\Finances\BillingController;
use App\Http\Controllers\Api\Finances\InvoiceController;
use App\Http\Controllers\Api\Finances\PaymentController;
use App\Models\Payment;


//untuk mengecek batch & student  dan submit registration
Route::prefix('registrations')->name('public.registrations.')->group(function () {
    Route::get('batch/{uuid}', [RegistrationController::class, 'showBatch'])->name('batch');
    Route::get('student/{nim}', [RegistrationController::class, 'showStudent'])->name('student');
    Route::post('/submit', [RegistrationController::class, 'submit'])->name('submit');
});

//menampilkan daftar tagihan by billing & student
Route::prefix('invoices')->name('public.invoices.')->group(function () {
    Route::get('billing/{uuid}', [InvoiceController::class, 'showByBilling'])->name('billing');
    Route::get('student/{nim}', [InvoiceController::class, 'showByNim'])->name('student');
});

//untuk submit payment
Route::prefix('payments')->name('public.payments.')->group(function () {
    Route::post('submit', [PaymentController::class, 'submit'])->name('submit');
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

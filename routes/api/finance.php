<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\Finances\RegistrationController;
use App\Http\Controllers\Api\Finances\BillingController;
use App\Http\Controllers\Api\Finances\InvoiceController;
use App\Http\Controllers\Api\Finances\PaymentController;

Route::prefix('finances')->middleware([
    'auth:api',
    'permission',
    'session.check',
    'is.in-active.user'
])->group(function () {


    // Registrations (biaya pendaftaran UKT)
    Route::prefix('registrations')
        ->name('registrations.')
        ->controller(RegistrationController::class)
        ->group(function () {
            Route::get('/', 'index')->name('index');
            Route::post('/', 'store')->name('store');
            Route::get('{id}', 'show')->name('show');
            Route::put('{id}', 'update')->name('update');
            Route::delete('{id}', 'destroy')->name('destroy');
        });

    // Billings
    Route::prefix('billings')
        ->name('billings.')
        ->controller(BillingController::class)
        ->group(function () {
            Route::get('/', 'index')->name('index');
            Route::post('/', 'store')->name('store');
            Route::get('{id}', 'show')->name('show');
            Route::put('{id}', 'update')->name('update');
            Route::delete('{id}', 'destroy')->name('destroy');
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

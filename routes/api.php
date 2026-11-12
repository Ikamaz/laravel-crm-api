<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CustomerController;
use App\Http\Controllers\Api\CustomerNoteController;
use App\Http\Controllers\Api\HealthController;
use App\Http\Controllers\Api\InboundLeadController;
use Illuminate\Support\Facades\Route;

Route::get('/health', HealthController::class)->name('health');

Route::prefix('auth')->name('auth.')->group(function () {
    Route::middleware('throttle:5,1')->group(function () {
        Route::post('/register', [AuthController::class, 'register'])->name('register');
        Route::post('/login', [AuthController::class, 'login'])->name('login');
    });

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
        Route::get('/me', [AuthController::class, 'me'])->name('me');
    });
});

Route::middleware('auth:sanctum')->group(function () {
    Route::apiResource('customers', CustomerController::class)
        ->only(['index', 'store', 'show', 'update', 'destroy']);

    Route::get('/customers/{customer}/notes', [CustomerNoteController::class, 'index'])
        ->name('customers.notes.index');
    Route::post('/customers/{customer}/notes', [CustomerNoteController::class, 'store'])
        ->name('customers.notes.store');
    Route::delete('/customers/{customer}/notes/{note}', [CustomerNoteController::class, 'destroy'])
        ->name('customers.notes.destroy');

    Route::apiResource('inbound-leads', InboundLeadController::class)
        ->only(['index', 'show', 'update']);
});

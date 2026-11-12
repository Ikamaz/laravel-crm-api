<?php

use App\Http\Controllers\AgencyLeadController;
use Illuminate\Support\Facades\Route;

Route::view('/', 'agency.home', ['locale' => 'ka'])->name('agency.home.ka');
Route::view('/en', 'agency.home', ['locale' => 'en'])->name('agency.home.en');

Route::post('/leads', [AgencyLeadController::class, 'store'])
    ->middleware('throttle:5,1')
    ->name('agency.leads.store');

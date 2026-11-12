<?php

use App\Http\Controllers\AgencyAdminController;
use App\Http\Controllers\AgencyLeadController;
use App\Models\InboundLead;
use Illuminate\Support\Facades\Route;

Route::view('/', 'agency.home', ['locale' => 'ka'])->name('agency.home.ka');
Route::view('/en', 'agency.home', ['locale' => 'en'])->name('agency.home.en');

Route::post('/leads', [AgencyLeadController::class, 'store'])
    ->middleware('throttle:5,1')
    ->name('agency.leads.store');

Route::prefix('admin')->name('agency.admin.')->group(function () {
    Route::get('/login', [AgencyAdminController::class, 'showLogin'])->name('login');
    Route::post('/login', [AgencyAdminController::class, 'login'])
        ->middleware('throttle:5,1')
        ->name('login.submit');

    Route::middleware(['auth', 'can:viewAny,'.InboundLead::class])->group(function () {
        Route::get('/leads', [AgencyAdminController::class, 'leads'])->name('leads');
        Route::patch('/leads/{inboundLead}', [AgencyAdminController::class, 'updateLeadStatus'])
            ->name('leads.update');
        Route::post('/logout', [AgencyAdminController::class, 'logout'])->name('logout');
    });
});

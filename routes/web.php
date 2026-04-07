<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\PersonnelController;
use App\Http\Controllers\PositionController;
use App\Http\Controllers\SchoolController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\EquipmentController;
use App\Http\Controllers\SpecialOrderController;
use App\Http\Controllers\TrainingController;
use App\Http\Controllers\InternetProfileController;
use App\Http\Controllers\IspInventoryController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\AgeMonitoringController;
use App\Http\Controllers\StepMonitoringController;

    // --- GUEST ROUTES (Not logged in) ---
    Route::middleware('guest')->group(function () {
        Route::get('/', [AuthController::class, 'showLoginForm'])->name('login');
        Route::post('/login', [AuthController::class, 'login']);
        Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
        Route::get('/logout', function (\Illuminate\Http\Request $request) {
            \Illuminate\Support\Facades\Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
            return redirect('/');
        });
    });

    // --- PROTECTED ROUTES (Logged in users only!) ---
    Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    
    // The Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/logs', [App\Http\Controllers\LogController::class, 'index'])->name('logs.index');

    // Personnel Module
    Route::resource('personnel', PersonnelController::class)
        ->parameters(['personnel' => 'personnel'])
        ->names('personnel');

    // Position Module
    Route::resource('positions', PositionController::class);

    // School Management
    Route::resource('schools', SchoolController::class);
    Route::get('/schools/{school}/profile/edit', [SchoolController::class, 'editProfile']);
    Route::post('/schools/{school}/profile/update', [SchoolController::class, 'updateProfile']);

    // User Management
    Route::resource('users', UserController::class);

    // Equipment Management
    Route::resource('equipment', EquipmentController::class);

    // Special Order
    Route::resource('specialorder', SpecialOrderController::class);

    // Training 
    Route::resource('training', TrainingController::class);

    // Internet Profiles
    Route::resource('internet', InternetProfileController::class)->only(['index', 'show', 'update']);

    // ISP Controllers
    Route::post('isp/{id}/speedtest', [IspInventoryController::class, 'storeSpeedTest'])->name('isp.speedtest');
    Route::resource('isp', IspInventoryController::class);

    // Districts CRUD
    Route::resource('districts', App\Http\Controllers\DistrictController::class);

    // Reports
    Route::get('reports', [ReportController::class, 'index'])->name('report.index');
    Route::get('reports/generate', [ReportController::class, 'generate'])->name('report.generate');

    // Monitoring
    Route::prefix('monitoring')->name('monitoring.')->group(function () {
        Route::get('/', fn () => redirect()->route('monitoring.step.year'))->name('index');

        Route::get('/step/year', [StepMonitoringController::class, 'year'])->name('step.year');
        Route::get('/step/month', [StepMonitoringController::class, 'month'])->name('step.month');

        Route::get('/age/actual', [AgeMonitoringController::class, 'actual'])->name('age.actual');
        Route::get('/age/year', [AgeMonitoringController::class, 'year'])->name('age.year');
        Route::get('/age/group', [AgeMonitoringController::class, 'ageGroup'])->name('age.group');
    });
});
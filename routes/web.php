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
use App\Http\Controllers\ISPInventoryController;

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
    //Route::get('/personnel', [PersonnelController::class, 'index']);
    //Route::get('/personnel/create', [PersonnelController::class, 'create']);
    //Route::post('/personnel', [PersonnelController::class, 'store']);

    Route::resource('personnel', PersonnelController::class)
        ->parameters(['personnel' => 'personnel'])
        ->names('personnel');

    // Backward-compatible entry points while links are transitioning from /employees to /personnel.
    Route::redirect('/employees', '/personnel');
    Route::redirect('/employees/create', '/personnel/create');
    Route::redirect('/employees/{personnel}', '/personnel/{personnel}');
    Route::redirect('/employees/{personnel}/edit', '/personnel/{personnel}/edit');

    // Position Module
    //Route::get('/positions', [PositionController::class, 'index']);
    //Route::get('/positions/create', [PositionController::class, 'create']);
    //Route::post('/positions', [PositionController::class, 'store']);
    //Route::put('/positions/{position}', [PositionController::class, 'update']);
    //Route::delete('/positions/{position}', [PositionController::class, 'destroy']); 

    Route::resource('positions', PositionController::class);

    // School Profile Module
    //Route::get('/schools', [SchoolController::class, 'index']);
    //Route::get('/schools/create', [SchoolController::class, 'create']);
    //Route::post('/schools', [SchoolController::class, 'store']);

    Route::resource('schools', SchoolController::class);
    Route::get('/schools/{school}/profile/edit', [SchoolController::class, 'editProfile']);
    Route::post('/schools/{school}/profile/update', [SchoolController::class, 'updateProfile']);

    // User Management

    Route::resource('users', UserController::class);

    // Equipment Management

    Route::resource('equipment', EquipmentController::class);

    // Special Orrder

    Route::resource('specialorder', SpecialOrderController::class);

    // Training 

    Route::resource('training', TrainingController::class);

    // Internet Profiles

    Route::resource('internet', InternetProfileController::class);

    // ISP Controllers
    Route::post('isp/{id}/speedtest', [ISPInventoryController::class, 'storeSpeedTest'])->name('isp.speedtest'); // hidden form apprently idk
    Route::resource('isp', ISPInventoryController::class);

});
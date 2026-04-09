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

// --- PROTECTED ROUTES (Logged in users only) ---
Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Personnel account convenience route
    Route::get('/my-profile', function () {
        $user = auth()->user();
        abort_unless($user && $user->hasRole('personnel') && $user->personnel_id, 403);

        return redirect()->route('personnel.show', $user->personnel_id);
    })->name('personnel.me');

    // Logs
    Route::get('/logs', [App\Http\Controllers\LogController::class, 'index'])
        ->name('logs.index')
        ->middleware('role:admin|school');

    // --- PERSONNEL ROUTES ---
    Route::middleware('role:admin|school')->group(function () {
        Route::get('/personnel/create', [PersonnelController::class, 'create'])->name('personnel.create');
        Route::post('/personnel', [PersonnelController::class, 'store'])->name('personnel.store');
        Route::get('/personnel/{personnel}/edit', [PersonnelController::class, 'edit'])->name('personnel.edit');
        Route::put('/personnel/{personnel}', [PersonnelController::class, 'update'])->name('personnel.update');
        Route::patch('/personnel/{personnel}', [PersonnelController::class, 'update']);
        Route::delete('/personnel/{personnel}', [PersonnelController::class, 'destroy'])->name('personnel.destroy');
    });
    Route::middleware('role:admin|school|encoding_officer')->group(function () {
        Route::get('/personnel', [PersonnelController::class, 'index'])->name('personnel.index');
   
        // Service Records CRUD (nested under personnel)
           Route::prefix('personnel/{personnel}/service-records')->name('service-records.')->group(function () {
               Route::post('/', [\App\Http\Controllers\ServiceRecordController::class, 'store'])->name('store');
               Route::put('{service_record}', [\App\Http\Controllers\ServiceRecordController::class, 'update'])->name('update');
               Route::delete('{service_record}', [\App\Http\Controllers\ServiceRecordController::class, 'destroy'])->name('destroy');
           });
        Route::get('/personnel/{personnel}/service-records/export/xlsx-format', [\App\Http\Controllers\ServiceRecordController::class, 'exportXlsxFormat'])->name('service-records.export.xlsx-format');
    });
    Route::middleware('role:admin|school|encoding_officer|personnel')->group(function () {
        Route::get('/personnel/{personnel}', [PersonnelController::class, 'show'])->name('personnel.show');
        Route::get('/personnel/{personnel}/pds/export', [PersonnelController::class, 'exportPds'])->name('personnel.pds.export');
    });

    // Positions (Admin only)
    Route::middleware('role:admin')->group(function () {
        Route::resource('positions', PositionController::class);
    });

    // School Profile
    Route::middleware('role:admin|school|encoding_officer')->group(function () {
        Route::get('/schools', [SchoolController::class, 'index'])->name('schools.index');
        Route::get('/schools/{school}', [SchoolController::class, 'show'])->name('schools.show');
    });
    Route::middleware('role:admin')->group(function () {
        Route::get('/schools/create', [SchoolController::class, 'create'])->name('schools.create');
        Route::post('/schools', [SchoolController::class, 'store'])->name('schools.store');
        Route::delete('/schools/{school}', [SchoolController::class, 'destroy'])->name('schools.destroy');
        Route::resource('districts', App\Http\Controllers\DistrictController::class);
    });
    Route::middleware('role:admin|school')->group(function () {
        Route::get('/schools/{school}/edit', [SchoolController::class, 'edit'])->name('schools.edit');
        Route::put('/schools/{school}', [SchoolController::class, 'update'])->name('schools.update');
        Route::patch('/schools/{school}', [SchoolController::class, 'update']);
        Route::get('/schools/{school}/profile/edit', [SchoolController::class, 'editProfile']);
        Route::post('/schools/{school}/profile/update', [SchoolController::class, 'updateProfile']);
    });

    // User Management (Admin + School)
    Route::middleware('role:admin|school')->group(function () {
        Route::resource('users', UserController::class)->except(['show']);
    });

    // Equipment (Admin + School)
    Route::middleware('role:admin|school')->group(function () {
        Route::resource('equipment', EquipmentController::class);
    });

    // Special Order (Admin full; School/EO/Personnel read-only for now)
    Route::middleware('role:admin|school|encoding_officer|personnel')->group(function () {
        Route::get('/specialorder', [SpecialOrderController::class, 'index'])->name('specialorder.index');
    });
    Route::middleware('role:admin')->group(function () {
        Route::get('/specialorder/create', [SpecialOrderController::class, 'create'])->name('specialorder.create');
        Route::post('/specialorder', [SpecialOrderController::class, 'store'])->name('specialorder.store');
        Route::get('/specialorder/{specialorder}/edit', [SpecialOrderController::class, 'edit'])->name('specialorder.edit');
        Route::put('/specialorder/{specialorder}', [SpecialOrderController::class, 'update'])->name('specialorder.update');
        Route::patch('/specialorder/{specialorder}', [SpecialOrderController::class, 'update']);
        Route::delete('/specialorder/{specialorder}', [SpecialOrderController::class, 'destroy'])->name('specialorder.destroy');
    });

    // Training (Admin/School CRUD; EO/Personnel read-only)
    Route::middleware('role:admin|school|encoding_officer|personnel')->group(function () {
        Route::get('/training', [TrainingController::class, 'index'])->name('training.index');
    });
    Route::middleware('role:admin|school')->group(function () {
        Route::get('/training/create', [TrainingController::class, 'create'])->name('training.create');
        Route::post('/training', [TrainingController::class, 'store'])->name('training.store');
        Route::get('/training/{training}/edit', [TrainingController::class, 'edit'])->name('training.edit');
        Route::put('/training/{training}', [TrainingController::class, 'update'])->name('training.update');
        Route::patch('/training/{training}', [TrainingController::class, 'update']);
        Route::delete('/training/{training}', [TrainingController::class, 'destroy'])->name('training.destroy');
    });

    // Internet Connectivity (Admin only)
    Route::middleware('role:admin')->group(function () {
        Route::resource('internet', InternetProfileController::class)->only(['index', 'show', 'update']);
        Route::post('isp/{id}/speedtest', [IspInventoryController::class, 'storeSpeedTest'])->name('isp.speedtest');
        Route::resource('isp', IspInventoryController::class);
    });

    // Reports (Admin + School)
    Route::middleware('role:admin|school')->group(function () {
        Route::get('reports', [ReportController::class, 'index'])->name('report.index');
        Route::get('reports/generate', [ReportController::class, 'generate'])->name('report.generate');
    });

    // Monitoring (Admin + School + EO)
    Route::middleware('role:admin|school|encoding_officer')->prefix('monitoring')->name('monitoring.')->group(function () {
        Route::get('/', fn () => redirect()->route('monitoring.step.year'))->name('index');

        Route::get('/step/year', [StepMonitoringController::class, 'year'])->name('step.year');
        Route::get('/step/month', [StepMonitoringController::class, 'month'])->name('step.month');

        Route::get('/age/actual', [AgeMonitoringController::class, 'actual'])->name('age.actual');
        Route::get('/age/year', [AgeMonitoringController::class, 'year'])->name('age.year');
        Route::get('/age/group', [AgeMonitoringController::class, 'ageGroup'])->name('age.group');
    });
});
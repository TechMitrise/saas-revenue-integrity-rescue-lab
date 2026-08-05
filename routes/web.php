<?php

use App\Http\Controllers\Api\AuditController;
use App\Http\Controllers\Api\DashboardDataController;
use App\Http\Controllers\Api\DemoResetController;
use App\Http\Controllers\Api\IncidentController;
use App\Http\Controllers\Api\IncidentNoteController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/app');

Route::middleware('guest')->group(function (): void {
    Route::get('/login', [AuthController::class, 'create'])->name('login');
    Route::post('/login', [AuthController::class, 'store'])->middleware('throttle:6,1');
});

Route::middleware('auth')->group(function (): void {
    Route::get('/app', DashboardController::class)->name('dashboard');
    Route::post('/logout', [AuthController::class, 'destroy'])->name('logout');

    Route::prefix('api')->group(function (): void {
        Route::get('/dashboard', DashboardDataController::class);
        Route::post('/audits', [AuditController::class, 'store']);
        Route::patch('/incidents/{incident}', [IncidentController::class, 'update']);
        Route::post('/incidents/{incident}/notes', [IncidentNoteController::class, 'store']);
        Route::get('/incidents/export.csv', [IncidentController::class, 'export']);
        Route::post('/demo/reset', DemoResetController::class);
    });
});

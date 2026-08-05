<?php

use App\Http\Controllers\Api\AuditController;
use App\Http\Controllers\Api\DashboardDataController;
use App\Http\Controllers\Api\DemoResetController;
use App\Http\Controllers\Api\GuidedDemoController;
use App\Http\Controllers\Api\ReconciliationController;
use App\Http\Controllers\Api\WebhookController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/proof');
Route::view('/proof', 'proof')->name('proof');
Route::post('/api/webhooks/synthetic-pay', [WebhookController::class, 'store'])->middleware('demo.mutations');

Route::middleware('guest')->group(function (): void {
    Route::get('/login', [AuthController::class, 'create'])->name('login');
    Route::post('/login', [AuthController::class, 'store'])->middleware('throttle:6,1');
});

Route::middleware('auth')->group(function (): void {
    Route::get('/app', DashboardController::class)->name('dashboard');
    Route::post('/logout', [AuthController::class, 'destroy'])->name('logout');

    Route::prefix('api')->group(function (): void {
        Route::get('/dashboard', DashboardDataController::class);

        Route::middleware('demo.mutations')->group(function (): void {
            Route::post('/audits', [AuditController::class, 'store']);
            Route::post('/incidents/{incident}/reconcile', [ReconciliationController::class, 'store']);
            Route::post('/demo/reset', DemoResetController::class);
            Route::post('/demo/guided/start', [GuidedDemoController::class, 'start']);
            Route::post('/demo/guided/advance', [GuidedDemoController::class, 'advance']);
        });
    });
});

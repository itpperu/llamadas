<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Web\ReportController;
use App\Http\Controllers\Web\AuthController;
use App\Http\Controllers\Web\VendedorController;
use App\Http\Controllers\Web\SettingsController;

// Rutas Públicas (Login)
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Rutas de recuperación de contraseña (solo para invitados no autenticados)
Route::middleware('guest')->group(function () {
    Route::get('/forgot-password', [AuthController::class, 'showForgotPassword'])->name('password.request');
    Route::post('/forgot-password', [AuthController::class, 'sendResetLink'])->name('password.email');
    Route::get('/reset-password/{token}', [AuthController::class, 'showResetForm'])->name('password.reset');
    Route::post('/reset-password', [AuthController::class, 'resetPassword'])->name('password.update');
});

// Rutas Protegidas (Panel Administrativo)
Route::middleware(['auth'])->group(function () {
    
    Route::get('/', function () {
        return redirect()->route('reports.index');
    });

    Route::prefix('reports')->group(function () {
        Route::get('/', [ReportController::class, 'index'])->name('reports.index');
        Route::get('/export', [ReportController::class, 'export'])->name('reports.export');
        Route::get('/export-package', [ReportController::class, 'exportPackage'])->name('reports.export-package');
        Route::get('/vendors', [ReportController::class, 'vendorSummary'])->name('reports.vendors');
        Route::get('/{call}', [ReportController::class, 'show'])->name('reports.show');
        Route::post('/{call}/reprocess', [ReportController::class, 'reprocess'])->name('reports.reprocess');
        Route::get('/{call}/audio', [ReportController::class, 'streamAudio'])->name('reports.audio');
    });

    Route::prefix('vendedores')->group(function () {
        Route::get('/', [VendedorController::class, 'index'])->name('vendedores.index');
        Route::get('/export', [VendedorController::class, 'export'])->name('vendedores.export');
        Route::get('/create', [VendedorController::class, 'create'])->name('vendedores.create');
        Route::post('/store', [VendedorController::class, 'store'])->name('vendedores.store');
        Route::get('/{vendedor}/edit', [VendedorController::class, 'edit'])->name('vendedores.edit');
        Route::put('/{vendedor}/update', [VendedorController::class, 'update'])->name('vendedores.update');
    });

    Route::prefix('settings')->group(function () {
        Route::get('/', [SettingsController::class, 'index'])->name('settings.index');
        Route::post('/api-tokens', [SettingsController::class, 'createApiToken'])->name('settings.api-tokens.create');
        Route::post('/api-tokens/{apiToken}/revoke', [SettingsController::class, 'revokeApiToken'])->name('settings.api-tokens.revoke');
    });

});

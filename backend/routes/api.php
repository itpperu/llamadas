<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CallController;
use App\Http\Controllers\Api\ExternalCallsController;

Route::prefix('auth')->group(function () {
    Route::post('/login', [AuthController::class, 'login']);
});

Route::middleware('auth:sanctum')->prefix('calls')->group(function () {
    Route::post('/', [CallController::class, 'store']);
    Route::post('/{call}/audio', [CallController::class, 'uploadAudio']);
    Route::post('/{call}/reprocess', [CallController::class, 'reprocess']);
});

// API externa — autenticada con token de api_tokens
Route::middleware(['api.token', 'throttle:60,1'])->prefix('v1')->group(function () {
    Route::get('/calls', [ExternalCallsController::class, 'index']);
});

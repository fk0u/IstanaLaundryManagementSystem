<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\OrderTrackingController;
use App\Http\Controllers\Api\ProductionController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// Public
Route::post('/login', [AuthController::class, 'login'])
    ->middleware('throttle:10,1')
    ->name('api.login');
Route::get('/track/{orderNumber}', [OrderTrackingController::class, 'show'])
    ->middleware('throttle:30,1')
    ->name('api.track');

// Authenticated (Sanctum token)
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('api.logout');
    Route::get('/me', [AuthController::class, 'me'])->name('api.me');

    Route::get('/production', [ProductionController::class, 'index'])->name('api.production.index');
    Route::get('/production/{order}', [ProductionController::class, 'show'])->name('api.production.show');
    Route::patch('/production/{order}/status', [ProductionController::class, 'updateStatus'])->name('api.production.update-status');
});

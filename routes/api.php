<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\OrderTrackingController;
use App\Http\Controllers\Api\ProductionController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\PublicApiController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// Public API Endpoints
Route::post('/login', [AuthController::class, 'login'])
    ->middleware('throttle:10,1')
    ->name('api.login');
Route::get('/track/{orderNumber}', [OrderTrackingController::class, 'show'])
    ->middleware('throttle:30,1')
    ->name('api.track');

// Public API v1 for Company Profile (Tracking & Online Order with GPS)
Route::prefix('v1')->group(function () {
    Route::get('/branches', [PublicApiController::class, 'branches'])->name('api.v1.branches');
    Route::get('/services', [PublicApiController::class, 'services'])->name('api.v1.services');
    Route::get('/track/{orderNumber?}', [PublicApiController::class, 'track'])->name('api.v1.track');
    Route::post('/track', [PublicApiController::class, 'track'])->name('api.v1.track.post');
    Route::post('/orders/online', [PublicApiController::class, 'storeOnlineOrder'])
        ->middleware('throttle:10,1')
        ->name('api.v1.orders.online');
});

// Authenticated (Sanctum token)
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('api.logout');
    Route::get('/me', [AuthController::class, 'me'])->name('api.me');

    // POS Tablet API
    Route::get('/pos/services', [\App\Http\Controllers\Api\PosTabletController::class, 'services'])->name('api.pos.services');
    Route::get('/pos/customers', [\App\Http\Controllers\Api\PosTabletController::class, 'customers'])->name('api.pos.customers');
    Route::post('/pos/orders', [\App\Http\Controllers\Api\PosTabletController::class, 'storeOrder'])->name('api.pos.store-order');

    Route::get('/production', [ProductionController::class, 'index'])->name('api.production.index');
    Route::get('/production/{order}', [ProductionController::class, 'show'])->name('api.production.show');
    Route::patch('/production/{order}/status', [ProductionController::class, 'updateStatus'])->name('api.production.update-status');
});

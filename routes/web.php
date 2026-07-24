<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// POS & Production Scoped Routes
Route::middleware(['auth', 'branch.scope'])->group(function () {
    // POS (Kasir)
    Route::get('/pos', [\App\Http\Controllers\POSController::class, 'index'])->name('pos.index');
    Route::post('/pos', [\App\Http\Controllers\POSController::class, 'store'])->name('pos.store');

    // Production Tracking
    Route::get('/production', [\App\Http\Controllers\ProductionController::class, 'index'])->name('production.index');
    Route::post('/production/update/{id}', [\App\Http\Controllers\ProductionController::class, 'updateStatus'])->name('production.update');
});

require __DIR__.'/auth.php';

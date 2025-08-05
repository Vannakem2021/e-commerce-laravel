<?php

use App\Http\Controllers\Settings\PasswordController;
use App\Http\Controllers\Settings\ProfileController;
use Illuminate\Support\Facades\Route;

// Profile and settings routes - accessible to all authenticated users
Route::middleware(['auth', 'verified'])->group(function () {
    // Main profile route - redirects to settings/profile for consistency
    Route::get('profile', [ProfileController::class, 'show'])->name('profile.show');

    // Settings routes
    Route::redirect('settings', '/settings/profile');

    Route::get('settings/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('settings/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::put('settings/profile', [ProfileController::class, 'update'])->name('profile.update.put');
    Route::delete('settings/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::get('settings/password', [PasswordController::class, 'edit'])->name('password.edit');
    Route::put('settings/password', [PasswordController::class, 'update'])->name('password.update');
});

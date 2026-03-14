<?php

use Illuminate\Support\Facades\Route;
use EpagesIntegration\Http\Controllers\OAuthController;
use EpagesIntegration\Http\Controllers\InstallController;

// ePages callback routes (called by ePages servers)
Route::middleware('web')->prefix('epages')->name('epages.')->group(function () {
    Route::get('/register', [InstallController::class, 'register'])->name('register');
    Route::delete('/unregister', [InstallController::class, 'unregister'])->name('unregister');
});

// Web routes (with session/middleware)
Route::middleware('web')->prefix('epages')->name('epages.')->group(function () {
    // OAuth routes
    Route::get('/authorize', [OAuthController::class, 'redirect'])->name('authorize');
    Route::get('/callback', [OAuthController::class, 'callback'])->name('callback');
    Route::post('/disconnect', [OAuthController::class, 'disconnect'])->name('disconnect');

    // Placeholder routes for install flow (to be implemented by the consuming app)
    Route::view('/install', 'epages::install')->name('install');
    Route::view('/success', 'epages::success')->name('success');
});

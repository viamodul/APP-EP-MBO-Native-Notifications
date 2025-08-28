<?php

use App\Http\Controllers\ShopController;
use App\Http\Controllers\WebhookController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    Route::apiResource('shops', ShopController::class);
    Route::post('shops/{shop}/test-connection', [ShopController::class, 'testConnection']);
    Route::post('shops/{shop}/poll-now', [ShopController::class, 'pollNow']);
    
    Route::get('webhook-logs', [WebhookController::class, 'index']);
    Route::post('webhook-logs/{webhookLog}/retry', [WebhookController::class, 'retry']);
});
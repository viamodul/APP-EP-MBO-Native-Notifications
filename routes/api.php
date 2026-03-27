<?php

use App\Http\Controllers\ShopController;
use App\Http\Controllers\WebhookController;
use Illuminate\Support\Facades\Route;

use App\Http\Middleware\EnsureValidAppToken;

Route::prefix('v1')->group(function () {
    Route::middleware([EnsureValidAppToken::class])->group(function () {
        Route::get('shops/lookup', [ShopController::class, 'lookup']);
        Route::delete('shops/remove', [ShopController::class, 'destroyByUrl']);
        Route::apiResource('shops', ShopController::class)->names('api.shops');
        Route::post('shops/{shop}/test-connection', [ShopController::class, 'testConnection']);
        Route::post('shops/{shop}/poll-now', [ShopController::class, 'pollNow']);
    });

    Route::get('webhook-logs', [WebhookController::class, 'index']);
    Route::post('webhook-logs/{webhookLog}/retry', [WebhookController::class, 'retry']);
});
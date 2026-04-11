<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\BillingController;
use App\Http\Controllers\LanguageController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\OnboardingController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PushSubscriptionController;
use App\Http\Controllers\ShopSettingsController;
use App\Http\Controllers\StripeWebhookController;
use App\Http\Controllers\WebhookLogController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/language/{locale}', [LanguageController::class, 'switch'])->name('language.switch');

// Dashboard - requires authentication
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Shop management
    Route::get('/shops/{shop}', [ShopSettingsController::class, 'show'])->name('shops.show');
    Route::get('/shops/{shop}/edit', [ShopSettingsController::class, 'edit'])->name('shops.edit');
    Route::patch('/shops/{shop}', [ShopSettingsController::class, 'update'])->name('shops.update');
    Route::post('/shops/{shop}/reactivate', [ShopSettingsController::class, 'reactivate'])->name('shops.reactivate');

    // Webhook logs
    Route::get('/shops/{shop}/webhooks', [WebhookLogController::class, 'index'])->name('shops.webhooks.index');
    Route::get('/shops/{shop}/webhooks/{webhookLog}', [WebhookLogController::class, 'show'])->name('shops.webhooks.show');
    Route::post('/shops/{shop}/webhooks/{webhookLog}/retry', [WebhookLogController::class, 'retry'])->name('shops.webhooks.retry');

    // Push notifications
    Route::get('/push/vapid-public-key', [PushSubscriptionController::class, 'getVapidPublicKey'])->name('push.vapid-key');
    Route::post('/push/subscribe', [PushSubscriptionController::class, 'subscribe'])->name('push.subscribe');
    Route::post('/push/unsubscribe', [PushSubscriptionController::class, 'unsubscribe'])->name('push.unsubscribe');
    Route::get('/push/check', [PushSubscriptionController::class, 'checkSubscription'])->name('push.check');
    Route::patch('/shops/{shop}/push', [PushSubscriptionController::class, 'toggleShopPush'])->name('shops.push.toggle');

    // Billing
    Route::get('/billing', [BillingController::class, 'index'])->name('billing.index');
    Route::get('/billing/portal', [BillingController::class, 'portal'])->name('billing.portal');
    Route::post('/billing/checkout', [BillingController::class, 'checkout'])->name('billing.checkout');
    Route::post('/billing/cancel', [BillingController::class, 'cancel'])->name('billing.cancel');
    Route::post('/billing/resume', [BillingController::class, 'resume'])->name('billing.resume');
});

// Profile routes
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// ePages App Store onboarding routes (no auth required - user registers here)
Route::prefix('epages/onboarding')->name('epages.onboarding.')->group(function () {
    Route::get('/register', [OnboardingController::class, 'showRegister'])->name('register');
    Route::post('/register', [OnboardingController::class, 'register'])->name('register.store');
    Route::get('/login', [OnboardingController::class, 'showLogin'])->name('login');
    Route::post('/login', [OnboardingController::class, 'login'])->name('login.store');
    Route::get('/success', [OnboardingController::class, 'showSuccess'])->name('success');
});

// Stripe webhooks (excluded from CSRF in bootstrap/app.php)
Route::post('/stripe/webhook', [StripeWebhookController::class, 'handleWebhook'])->name('stripe.webhook');

// Admin routes
Route::middleware(['auth', 'verified', \App\Http\Middleware\IsAdmin::class])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [AdminController::class, 'index'])->name('dashboard');
    Route::get('/users/{user}', [AdminController::class, 'showUser'])->name('users.show');
    Route::patch('/users/{user}', [AdminController::class, 'updateUser'])->name('users.update');
});

require __DIR__.'/auth.php';

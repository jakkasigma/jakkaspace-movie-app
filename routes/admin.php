<?php

use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\PlanController;
use App\Http\Controllers\Admin\PromoController;
use App\Http\Controllers\Admin\PromoRedeemController;
use App\Http\Controllers\Admin\RedeemCodeController;
use App\Http\Controllers\Admin\SubscriptionController;
use App\Http\Controllers\Admin\ThemeController;
use App\Http\Controllers\Admin\UserController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    Route::resource('themes', ThemeController::class)->except('show');

    Route::get('/subscriptions', [SubscriptionController::class, 'index'])->name('subscriptions.index');
    Route::get('/subscriptions/transactions', [SubscriptionController::class, 'transactions'])->name('subscriptions.transactions');
    Route::post('/subscriptions/grant', [SubscriptionController::class, 'grant'])->name('subscriptions.grant');
    Route::delete('/subscriptions/{user}/cancel', [SubscriptionController::class, 'cancel'])->name('subscriptions.cancel');
    Route::post('/subscriptions/{user}/extend', [SubscriptionController::class, 'extend'])->name('subscriptions.extend');

    Route::get('/users', [UserController::class, 'index'])->name('users.index');
    Route::post('/users/{user}/ban', [UserController::class, 'ban'])->name('users.ban');
    Route::post('/users/{user}/unban', [UserController::class, 'unban'])->name('users.unban');

    Route::get('/plans', [PlanController::class, 'index'])->name('plans.index');
    Route::get('/plans/{plan}/edit', [PlanController::class, 'edit'])->name('plans.edit');
    Route::post('/plans', [PlanController::class, 'store'])->name('plans.store');
    Route::put('/plans/{plan}', [PlanController::class, 'update'])->name('plans.update');
    Route::delete('/plans/{plan}', [PlanController::class, 'destroy'])->name('plans.destroy');
    Route::post('/plans/{plan}/toggle-active', [PlanController::class, 'toggleActive'])->name('plans.toggle-active');

    // Promo & Redeem — gabung 1 menu
    Route::get('/promo-redeem', [PromoRedeemController::class, 'index'])->name('promo-redeem.index');

    // Promos
    Route::post('/promo-redeem/promos', [PromoController::class, 'store'])->name('promo-redeem.promos.store');
    Route::put('/promo-redeem/promos/{promo}', [PromoController::class, 'update'])->name('promo-redeem.promos.update');
    Route::post('/promo-redeem/promos/{promo}/activate', [PromoController::class, 'activate'])->name('promo-redeem.promos.activate');
    Route::delete('/promo-redeem/promos/{promo}', [PromoController::class, 'destroy'])->name('promo-redeem.promos.destroy');

    // Redeem codes
    Route::post('/promo-redeem/redeem-codes', [RedeemCodeController::class, 'store'])->name('promo-redeem.redeem-codes.store');
    Route::get('/promo-redeem/redeem-codes/{redeemCode}', [RedeemCodeController::class, 'show'])->name('promo-redeem.redeem-codes.show');
    Route::post('/promo-redeem/redeem-codes/{redeemCode}/activate', [RedeemCodeController::class, 'activate'])->name('promo-redeem.redeem-codes.activate');
    Route::delete('/promo-redeem/redeem-codes/{redeemCode}', [RedeemCodeController::class, 'destroy'])->name('promo-redeem.redeem-codes.destroy');
});
